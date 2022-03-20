<?php

namespace App\OnlineFriends\Model;

class Friend
{
    public string $username;

    public string $displayName;

    public function __construct(string $username, string $displayName)
    {
        $this->username    = $username;
        $this->displayName = $displayName;
    }
}
