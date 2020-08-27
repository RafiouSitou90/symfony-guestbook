<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AppFixtures extends Fixture
{
    /**
     * @var EncoderFactoryInterface
     */
    private EncoderFactoryInterface $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadConferences($manager);
        $this->loadAdmins($manager);
    }

    private function loadConferences (ObjectManager $manager)
    {
        $toronto = (new Conference())
            ->setCity('Toronto')
            ->setYear('2020')
            ->setIsInternational(true)
        ;
        $manager->persist($toronto);

        $brasilia = (new Conference())
            ->setCity('Brasilia')
            ->setYear('2021')
            ->setIsInternational(false)
        ;
        $manager->persist($brasilia);

        $paris = (new Conference())
            ->setCity('Paris')
            ->setYear('2022')
            ->setIsInternational(true)
        ;
        $manager->persist($paris);

        for ($i = 0; $i < 10; $i++) {
            $comment = (new Comment())
                ->setAuthor('Rafiou')
                ->setEmail('rafiou@domain.com')
                ->setText('This was a great conference ever seen')
                ->setConference($toronto)
            ;
            $manager->persist($comment);
        }

        $manager->flush();
    }

    private function loadAdmins (ObjectManager $manager)
    {
        $admin = (new Admin())
            ->setUsername('admin')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword(
                $this->encoderFactory->getEncoder(Admin::class)->encodePassword('123456789', null)
            )
        ;
        $manager->persist($admin);
        $manager->flush();
    }
}
