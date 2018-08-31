<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Faucet;
use DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

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
		$faucet->setBanUntil( new DateTime('-1 day') );

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
		$faucet->setIsTab( (bool)$form_data->getIsTab() );
		$faucet->setBanUntil( date_create_from_format('Y-m-d H:i:s', date('Y-m-d', strtotime('+'.$form_data->bandays.' day')).' 00:00:00' ) );

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
			->where(
				$qb->expr()->andX(
					$qb->expr()->gte('timestamp_diff( SECOND, fct.until, CURRENT_TIMESTAMP())', 0),
					$qb->expr()->gte('timestamp_diff( SECOND, fct.banUntil, CURRENT_TIMESTAMP())', 0)
				)
			)
		;

		return $qb;
	}
//______________________________________________________________________________

	public function getFirstReadyFaucet(){
		$session = new Session(new NativeSessionStorage(), new AttributeBag());
		$order = $session->get('order', 'desc');

		$qb		= $this->getActiveFaucetsObj()
			->setMaxResults( 1 )
			->addSelect('RAND() as HIDDEN rand')
			->addOrderBy('fct.priority', $order )
			->addOrderBy('rand', 'ASC')
			;

		$query	= $qb->getQuery();
		$res	= $query->getResult();

		$ret_val	= $res[0] ?? $this->getNullFaucet();
// 		$ret_val	= $this->getNullFaucet();			//	Debug
		return $ret_val;
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

		$faucet->setUntil( new DateTime( '+'.$data['cduration'].' minute' ) );
		$faucet->setPriority( $data['priority'] );		//Priority is updated for comfort when next faucet is quered.

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

	public function updateUntilTomorrow( $data ){
		$faucet	= $this->_em->getRepository(Faucet::class)->find( $data['id'] );

		$faucet->setUntil( new DateTime('+1 day') );
		$faucet->setIsDebt( true );
		$faucet->setPriority( $data['priority'] );		//Priority is updated for comfort

		$this->_em->flush();
		return true;
	}
//______________________________________________________________________________

	public function updateDuration( $data ){
		$faucet	= $this->_em->getRepository(Faucet::class)->find( $data['id'] );
		$faucet->setDuration( $data['cduration'] * 60 );
		$this->_em->flush();
		return true;
	}
//______________________________________________________________________________

	public function updateDebt( $data ){
		$faucet	= $this->_em->getRepository(Faucet::class)->find( $data['id'] );
		$faucet->setIsDebt( !(bool)$faucet->getIsDebt() );
		$this->_em->flush();
		return true;
	}
//______________________________________________________________________________

	public function processFaucet( $faucet ){

		if( !is_array($faucet) )
			return $faucet;

			foreach( $faucet as $key=>$value ){
				if( (string)$key == '0' ){
					$res_faucet	= $value;
				}else{
					$res_faucet->{$key}	= $value;
				}
			}


		return $res_faucet;

	}
//______________________________________________________________________________

	public function getFaucetsInfo(){

		$qb = $this->_em->createQueryBuilder();
		$faucets	= $qb
			->select('fct,'.
					'(2 + 2) AS dummy '.
					',IF(timestamp_diff( SECOND, fct.until, CURRENT_TIMESTAMP()) < 0, true, false) AS is_leter'.
					',IF(timestamp_diff( SECOND, fct.banUntil, CURRENT_TIMESTAMP()) < 0, true, false) AS is_ban'.
					'')
			->from('AppBundle\Entity\Faucet', 'fct')
// 			->setMaxResults(5)
			->getQuery()->getResult();

		foreach( $faucets as &$faucet ){
			$faucet	= $this->processFaucet( $faucet );
		}

		return $faucets;
	}
//______________________________________________________________________________

}
