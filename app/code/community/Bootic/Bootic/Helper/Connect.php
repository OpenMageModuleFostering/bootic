<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Connect extends Bootic_Bootic_Helper_Abstract
{
    public function getProfile()
    {
        return $this->getBootic()->getProfile();
    }

    public function editProfile(array $profile)
    {
        return $this->getBootic()->editProfile($profile);
    }

    public function editProfilePicture(array $data)
    {
        return $this->getBootic()->editProfilePicture($data);
    }
}
