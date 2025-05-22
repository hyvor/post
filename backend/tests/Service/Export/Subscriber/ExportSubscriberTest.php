<?php

namespace App\Tests\Service\Export\Subscriber;

use App\Service\Subscriber\SubscriberCsvExporter;
use App\Tests\Case\KernelTestCase;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\Configurator\Traits\FactoryTrait;

class ExportSubscriberTest extends KernelTestCase
{
    public function test_export_subscriber(): void
    {
        $project = ProjectFactory::createOne();

        $subscribers = SubscriberFactory::createMany(50, [
            'project' => $project,
        ]);

        $subscriberMetadata  = SubscriberMetadataDefinitionFactory::createOne([
            'key' => 'Phone Number',
            'project' => $project,
        ]);

        $exporter = new SubscriberCsvExporter(
            $this->em
        );

        $file = $exporter->createFile($project->_real());
        $read = file_get_contents($file);
        $this->assertNotFalse($read);
        $lines = explode("\n", $read);

        $this->assertSame(52, count($lines));
        $this->assertSame('Email,Status,"Subscribed At",Source,"Phone Number"', $lines[0]);

        $data = explode(',', $lines[1]);
        $this->assertSame(4, count($data));
        $this->assertSame($subscribers[0]->getEmail(), $data[0]);
        $this->assertSame($subscribers[0]->getStatus()->value, $data[1]);
        $this->assertNotNull($subscribers[0]->getSubscribedAt());
        $this->assertSame('"' . $subscribers[0]->getSubscribedAt()->format('Y-m-d H:i:s') . '"', $data[2]);
        $this->assertSame($subscribers[0]->getSource()->value, $data[3]);
    }
}
