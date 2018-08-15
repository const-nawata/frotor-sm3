<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Faucet;
use DateTime;

/**
 * FaucetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FaucetRepository extends \Doctrine\ORM\EntityRepository{


	public function getNullFaucet(){
		$faucet	= new Faucet();
		$dt_now	= new DateTime();

		$faucet->setUrl('');
		$faucet->setDuration( 1800 );

		$faucet->setUpdated( $dt_now );
		$faucet->setUntil( $dt_now );
		$faucet->setBanUntil( new DateTime(date('Y-m-d H:i:s', strtotime( '-1 day' ))) );

		$faucet->setPriority( 1 );
		$faucet->setIsDebt( false );

		return $faucet;
	}
//______________________________________________________________________________



}
