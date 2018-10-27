<?php

namespace App\Security\Voter;

use App\Entity\Restaurant;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RestaurantVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Restaurant;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // if the user is anonymous, do not grant access
                if (!$user instanceof User) {
                    return false;
                }
                return $this->canEdit($subject, $user);
                break;
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        return false;
    }

    public function canView(Restaurant $restaurant, $user)
    {
        if ($user instanceof User)
        {
            if ($user == $restaurant->getOwner())
            {
                return true;
            }elseif ($restaurant->isEnabled())
            {
                return true;
            }
        }else
        {
            return $restaurant->isEnabled();
        }

    }

    public function canEdit(Restaurant $restaurant, User $user)
    {
        return $user === $restaurant->getOwner();
    }

}
