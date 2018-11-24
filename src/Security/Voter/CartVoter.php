<?php

namespace App\Security\Voter;

use App\Entity\Cart;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CartVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Cart;
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
            case 'EDIT':
                $this->canEdit($subject, $user);
                break;
            case 'VIEW':
                $this->canView($subject, $user);
                break;
        }

        return false;
    }


    public function canView(Cart $cart, $user)
    {
        return $cart === $user->getCart();
    }


    public function canEdit(Cart $cart, $user){
        if ($cart !== $user->getCart()){
            return false;
        }

        if ($cart->getMenus()->count() <= 0){
            return false;
        }

        return true;
    }

}
