<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC')
                        ->where('u.roles LIKE :roles')
                        ->setParameter('roles', '%"'."ROLE_WORKER".'"%');
                },
                'choice_label' => 'username',])
            ->add('start_time', DateTimeType::class)
            ->add('end_time', DateTimeType::class)
            ->add('break_length', IntegerType::class)
            ->add('customer', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC')
                        ->where('u.roles LIKE :roles')
                        ->setParameter('roles', '%"'."ROLE_CUSTOMER".'"%');
                },
                'choice_label' => 'companyname',])
            ->add('used_materials', TextType::class)
            ->add('transport_distance', IntegerType::class)
            ->add('activity_description', TextType::class)
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
