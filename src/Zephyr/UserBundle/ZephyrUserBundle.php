<?php

namespace Zephyr\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZephyrUserBundle extends Bundle
{
	public function getParent()
  	{
    	return 'FOSUserBundle';
  	}
}
