<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{

    const VIEW = 'view';
    const EDIT = 'edit';
    const VISIT = 'visit';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::EDIT, self::VISIT])
            && $subject instanceof User;
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
            case self::VIEW:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::EDIT:
                // logic to determine if the user can VIEW
                // return true or false
                break;
            case self::VISIT:
                return $this->canAdminVisit($subject, $user);
                break;
        }

        return false;
    }

    public function canAdminVisit(User $user, $currentUser)
    {
        if (!$currentUser->hasRole('ROLE_ADMIN'))
        {
            return false;
        }

        if ($user->getId() === $currentUser->getId())
        {
            return false;
        }
        return true;
    }

}
