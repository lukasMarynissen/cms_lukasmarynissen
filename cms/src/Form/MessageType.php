<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\Period;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('created_at',DateType::class)
            ->add('content', TextType::class)
            ->add('sender_type', TextType::class)
            ->add('sender', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'companyname'])
            ->add('recipient',EntityType::class, [
        'class' => User::class,
        'choice_label' => 'companyname'])
            ->add('related_period', EntityType::class, [
                'class' => Period::class,
                'choice_label' => 'id'])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
