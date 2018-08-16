<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Faucet;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;

/**
 * FaucetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
// class FaucetRepository extends \Doctrine\ORM\EntityRepository
class FaucetRepository extends ServiceEntityRepository{

	private $lg;

	public function __construct( RegistryInterface $registry, LoggerInterface $logger ){
        parent::__construct($registry, Faucet::class);

        $this->lg	= $logger;
    }

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

	public function removeFaucet( $id ){
		$faucet	= $this->_em->getRepository(Faucet::class)->find( $id );
		$this->_em->remove( $faucet );
		$this->_em->flush();
	}
//______________________________________________________________________________

    private static function prepareUrl( $form_data ){
    	$url	= $form_data->getUrl();

    	$query	= parse_url( $url, PHP_URL_QUERY );
		$url	= parse_url( $url, PHP_URL_SCHEME ).'://'.parse_url( $url, PHP_URL_HOST ).parse_url( $url, PHP_URL_PATH );

		$form_data->setUrl( $url );
		$form_data->setQuery( $query );

    	return $form_data;
    }
//______________________________________________________________________________

	public function saveFaucet( $id, $form_data ){

		$form_data	= self::prepareUrl( $form_data );

		$faucet = (bool)$id
			? $this->_em->getRepository(Faucet::class)->find( $id )
			: $this->_em->getRepository(Faucet::class)->getNullFaucet();

		$faucet->setUrl( $form_data->getUrl() );
		$faucet->setQuery( $form_data->getQuery() );
		$faucet->setInfo( $form_data->getInfo() );
		$faucet->setPriority( $form_data->getPriority() );
		$faucet->setDuration( $form_data->getDuration() * 60 );

		!(bool)$id ? $this->_em->persist($faucet):null;
		$this->_em->flush();

		return true;
	}
//______________________________________________________________________________

	private static function applyTimeUnit( $faucet ){
    	$duration = $faucet->getDuration() / 60;
    	$faucet->setDuration($duration);
    	return $faucet;
	}
//______________________________________________________________________________

	public function prepareFaucet( $faucet ){
		$url	= $faucet->getUrl().($faucet->getQuery() != '' ? '?'.$faucet->getQuery() : '');
		$faucet->setUrl( $url );

		$dt_now			= new DateTime();
		$dt_ban 		= $faucet->getBanUntil();
		$diff			= $dt_now->diff( $dt_ban, FALSE );
		$faucet->bandays= $diff->invert ? 0 : $diff->d;

		return self::applyTimeUnit( $faucet );;
	}
//______________________________________________________________________________

	private function getActiveFaucetsObj(){
		$qb = $this->_em->createQueryBuilder();

		$qb->select('fct')->from('AppBundle\Entity\Faucet', 'fct')
			->where($qb->expr()->andX(
				$qb->expr()->gte('timestamp_diff( SECOND, fct.until, CURRENT_TIMESTAMP())', 0)
				,$qb->expr()->gte('timestamp_diff( SECOND, fct.banUntil, CURRENT_TIMESTAMP())', 0)
			)
		)
		;

		return $qb;
	}
//______________________________________________________________________________

	public function getFirstReadyFaucet(){
		$qb		= $this->getActiveFaucetsObj()
			->setMaxResults( 1 )
			->addSelect('RAND() as HIDDEN rand')
			->addOrderBy('fct.priority', 'DESC')
			->addOrderBy('rand', 'ASC')
			;

		$query	= $qb->getQuery();
		$res	= $query->getResult();
		return $res[0];
	}
//______________________________________________________________________________

	public function faucetCount(){
		return [
			'n_act' => count($this->getActiveFaucetsObj()->getQuery()->getResult()),
			'n_all' => count($this->_em->getRepository(Faucet::class)->findAll())];
	}
//______________________________________________________________________________

	public function updateUntil( $data ){

		$faucet	= $this->_em->getRepository(Faucet::class)->find( $data['id'] );

		if( !$faucet->is_debt ){
			$updated	= new DateTime();
			$faucet->setUpdated( $updated );
		}

		$until	= new DateTime(date('Y-m-d H:i:s', strtotime( '+'.$data['cduration'].' minute' )));
		$faucet->setUntil( $until );

		$faucet->setPriority( $data['priority'] );

		$this->_em->flush();

		return true;
	}
//______________________________________________________________________________

	public function resetAll( $data ){
		$date	= new DateTime();
		$qb		= $this->_em->createQueryBuilder();

		try {
			$query	= $qb
				->update( Faucet::class, 'f' )
				->set( 'f.until', '?1' )
	        	->where('f.until > ?2')

	        	->setParameter(1, $date)
	        	->setParameter(2, $date)

				->getQuery();

//	 			$sql	= $query->getSQL();				//XXX: Left for information.

			$res	= $query->execute();

		} catch (\Exception $e) {
			return -1;
		}

        return $res;
	}
//______________________________________________________________________________



}
