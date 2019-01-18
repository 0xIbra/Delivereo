<?php

namespace App\Security\Voter;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Order;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        return false;
    }


    public function canEdit(Order $order, $user)
    {
        if ($order->getConsumer()->getId() === $user->getId())
        {
            return true;
        }

        if ($user->hasRole('ROLE_ADMIN'))
        {
            return true;
        }

        return false;
    }

    public function canView(Order $order, $user)
    {
        if ($user->hasRole('ROLE_ADMIN'))
        {
            return true;
        }

        if ($order->getConsumer()->getId() === $user->getId())
        {
            return true;
        }

        if ($order->getRestaurants()->contains($user->getRestaurant()) && $user->hasRole('ROLE_OWNER'))
        {
            return true;
        }

        return false;
    }

}
