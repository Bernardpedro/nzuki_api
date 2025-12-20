<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_SECRET = "
              f33c9ab615d07f0f8ba7f75eafc71482c9943a550d73
              143a111a3eb1167e9ca869e2334dd0b8b57ed15
              b1fb3e71cac6341f77b8befae21af0b3c69808030129e
              ";

$JWT_ISSUER = "nzukiApi";
$JWT_EXPIRE = time() + (60 * 60); 