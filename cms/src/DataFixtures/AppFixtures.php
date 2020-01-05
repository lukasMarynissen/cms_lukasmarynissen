<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Rate;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    protected $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $this->faker = Factory::create();

        /*
         * USERS
         */


        //WORKERS
        $workers = [];
        for($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($this->faker->safeEmail);
            $user->setName($this->faker->lastName);
            $user->setFirstName($this->faker->firstName);
            $user->setRoles(["ROLE_WORKER"]);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'secret'
            ));
            $manager->persist($user);
            array_push($workers, $user);
        }

        //CUSTOMERS
        $customers = [];
        for($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($this->faker->safeEmail);
            $user->setName($this->faker->lastName);
            $user->setFirstName($this->faker->firstName);
            $user->setCompanyName($this->faker->company);
            $user->setRoles(["ROLE_CUSTOMER"]);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'secret'
            ));
            $manager->persist($user);
            array_push($customers, $user);
        }

        //ADMIN
        $user = new User();
        $user->setEmail("admin@artetech.be");
        $user->setName("Baasmans");
        $user->setFirstName("Jonas");
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'secret'
        ));
        $manager->persist($user);


        //WORKER
        $worker3 = new User();
        $worker3->setEmail("b@b.b");
        $worker3->setName("Werkmans");
        $worker3->setFirstName("Bart");
        $worker3->setRoles(["ROLE_WORKER"]);
        $worker3->setPassword($this->passwordEncoder->encodePassword(
            $worker3,
            'secret'
        ));
        $manager->persist($worker3);
        array_push($workers, $worker3);

        //CUSTOMER
        $customer = new User();
        $customer->setEmail("c@c.c");
        $customer->setName("Klanters");
        $customer->setFirstName("Celine");
        $customer->setCompanyName("Customer One BVBA");
        $customer->setRoles(["ROLE_CUSTOMER"]);
        $customer->setPassword($this->passwordEncoder->encodePassword(
            $customer,
            'secret'
        ));
        $manager->persist($customer);
        array_push($customers, $customer);

        $manager->flush();


        /*
         *  RATES
         */

        foreach ($customers as $customer){

            $rate = new Rate();
            $rate->setCustomer($customer);
            $rate->setHourlyRate(35);
            $rate->setTransportCostRate(0.3);
            $manager->persist($rate);

        }

        $manager->flush();

        /*
         * ACTIVITIES
         */

        $materials = [
            "Schroeven", "Kabels", "Sensoren", "Chalarmeermateriaal"
        ];

        $descriptions = [
            "Vernieuwen sensoren.", "Vervangen van verouderde kabels.", "Installatie nieuwe sensoren.", "Onderhoud elektrische bekabeling.", "Onderhoud sensoren",
        ];


        for($i = 0; $i < 60; $i++) {

            $activity = new Activity();
            $activity->setUser($workers[array_rand($workers)]);
            $activity->setStartTime($this->faker->dateTimeBetween($startDate = '-5 days', $endDate = '+5 days'));
            $stime = clone $activity->getStartTime();
            $activity->setEndTime($stime->modify('+'.$this->faker->numberBetween($min = 1, $max = 5).'hour'));
            $activity->setBreakLength($this->faker->numberBetween($min = 0, $max = 15)*5);
            $activity->setCustomer($customers[array_rand($customers)]);
            $activity->setUsedMaterials($materials[array_rand($materials)]);
            $activity->setTransportDistance($this->faker->numberBetween($min = 0, $max = 150));
            $activity->setActivityDescription($descriptions[array_rand($descriptions)]);
            $manager->persist($activity);

        }

        $manager->flush();

    }
}
