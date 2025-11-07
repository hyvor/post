<?php

namespace App\Service\Subscriber;

use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\SubscriberMetadataDefinition;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\assertArrayHasKey;

class SubscriberCsvExporter
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    protected function getTemporaryFile(string $extension): string
    {
        $file = tmpfile();

        if (!$file) {
            throw new \Exception('Could not create temporary file.');
        }

        $meta = stream_get_meta_data($file);

        if (!isset($meta['uri'])) {
            throw new \Exception('Could not get temporary file path.');
        }

        $path = $meta['uri'];
        fclose($file);
        return $path . '.' . $extension;
    }

    public function createFile(Newsletter $newsletter): string
    {
        $csv = $this->getTemporaryFile('csv');

        $file = fopen($csv, 'w');

        if (!$file) {
            throw new \Exception('Could not create file');
        }

        $subscriberMetadata = $this->em->getRepository(SubscriberMetadataDefinition::class)->findBy(['newsletter' => $newsletter], ['id' => 'ASC']);
        $headers = ['Email', 'Status', 'Subscribed At', 'Source'];
        foreach ($subscriberMetadata as $sb) {
            $headers[] = $sb->getKey();
        }

        fputcsv($file, $headers);

        $batchSize = 1000;
        $offset = 0;

        do {
            /** @var Subscriber[] $subscribers */
            $subscribers = $this->em->getRepository(Subscriber::class)
                ->createQueryBuilder('s')
                ->where('s.newsletter = :newsletter')
                ->setParameter('newsletter', $newsletter)
                ->orderBy('s.id', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->getEmail(),
                    $subscriber->getStatus()->value,
                    $subscriber->getSubscribedAt()?->format('Y-m-d H:i:s') ?? '',
                    $subscriber->getSource()->value,
                    ...array_map(fn($md) => $subscriber->getMetadata()[$md->getKey()] ?? '', $subscriberMetadata),
                ]);
            }

            $offset += $batchSize;
        } while ($subscribers && count($subscribers) === $batchSize);

        return $csv;
    }
}