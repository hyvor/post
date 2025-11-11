<?php

namespace App\Service\UserInvite;

use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Entity\UserInvite;
use App\Service\AppConfig;
use App\Service\SystemMail\SystemNotificationMailService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Internationalization\StringsFactory;
use Symfony\Component\Clock\ClockAwareTrait;
use Twig\Environment;

class UserInviteService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface        $em,
        private SystemNotificationMailService $emailNotificationService,
        private readonly Environment          $mailTemplate,
        private readonly StringsFactory       $stringsFactory,
        private readonly AppConfig            $appConfig,
    )
    {
    }

    /**
     * @return ArrayCollection<int, UserInvite>
     */
    public function getNewsletterInvites(Newsletter $newsletter): ArrayCollection
    {
        $userInvites = $this->em->getRepository(UserInvite::class)->findBy([
            'newsletter' => $newsletter,
        ]);

        if (!$userInvites) {
            return new ArrayCollection();
        }

        return new ArrayCollection($userInvites);
    }

    public function createInvite(
        Newsletter $newsletter,
        int        $hyvorUserId,
        UserRole   $role,
    ): UserInvite
    {
        $userInvite = new UserInvite();
        $userInvite->setCreatedAt(new \DateTimeImmutable());
        $userInvite->setUpdatedAt(new \DateTimeImmutable());
        $userInvite->setNewsletter($newsletter);
        $userInvite->setHyvorUserId($hyvorUserId);
        $userInvite->setCode(bin2hex(random_bytes(16)));
        $userInvite->setExpiresAt($this->now()->add(new \DateInterval('P1D')));
        $userInvite->setRole($role);

        $this->em->persist($userInvite);
        $this->em->flush();

        return $userInvite;
    }

    public function sendEmail(AuthUser $hyvorUser, UserInvite $userInvite): void
    {
        $strings = $this->stringsFactory->create();

        $mail = $this->mailTemplate->render('mail/user_invite.html.twig', [
            'component' => 'post',
            'buttonUrl' => $this->appConfig->getUrlApp() . '/api/public/invite/verify?code=' . $userInvite->getCode(),
            'strings' => [
                'greeting' => $strings->get('mail.common.greeting', ['name' => $hyvorUser->name]),
                'subject' => $strings->get(
                    'mail.userInvite.subject',
                    ['newsletterName' => $userInvite->getNewsletter()->getName()]
                ),
                'text' => $strings->get(
                    'mail.userInvite.text',
                    ['newsletterName' => $userInvite->getNewsletter()->getName(), 'role' => 'admin']
                ),
                'buttonText' => $strings->get('mail.userInvite.buttonText'),
                'footerText' => $strings->get('mail.userInvite.footerText'),
            ]
        ]);

        $this->emailNotificationService->send(
            $hyvorUser->email,
            $strings->get('mail.userInvite.subject', ['newsletterName' => $userInvite->getNewsletter()->getName()]),
            $mail,
        );
    }

    public function isInvited(Newsletter $newsletter, int $hyvorUserId): bool
    {
        $userInvite = $this->em->getRepository(UserInvite::class)->findBy([
            'newsletter' => $newsletter,
            'hyvor_user_id' => $hyvorUserId,
        ]);

        if (!$userInvite) {
            return false;
        }
        return true;
    }

    public function getInviteFromCode(string $code): ?UserInvite
    {
        return $this->em->getRepository(UserInvite::class)->findOneBy([
            'code' => $code,
        ]);
    }

    public function deleteInvite(UserInvite $userInvite): void
    {
        $this->em->remove($userInvite);
        $this->em->flush();
    }

    public function extendInvite(int $userId): UserInvite
    {
        $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['hyvor_user_id' => $userId]);
        if (!$userInvite) {
            throw new \RuntimeException("User invite not found");
        }
        $userInvite->setExpiresAt($this->now()->add(new \DateInterval('P1D')));
        $this->em->flush();
        return $userInvite;
    }
}
