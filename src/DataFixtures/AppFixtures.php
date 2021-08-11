<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)

    {   

        #### Ajout role admin  ######
        $role_admin = new Role();
        $role_admin->setLibelle("ROLE_ADMIN");
        $manager->persist($role_admin);

        #### Ajout role caissier  ######
        $role_caissier = new Role();
        $role_caissier->setLibelle("ROLE_GERANT");
        $manager->persist($role_caissier);

        #### Ajout role apartenaire ######
        $role_partenaire = new Role();
        $role_partenaire->setLibelle("ROLE_CLIENT");
        $manager->persist($role_partenaire);

        #### Ajout un admin system ######
        $user = new User();
        $user->setUsername("beedigital")
            ->setRole($role_admin)
            ->setPassword($this->encoder->encodePassword($user, "admin123"))
            ->setNomComplet("Bee Digital")
            ->setTelephone("773043248")
            ->setUpdatedAt(new \DateTime())
            ->setCreatedAt(new \DAteTime());
        $manager->persist($user);
        $manager->flush();
    }
}
