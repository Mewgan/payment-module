<?php

namespace Jet\Modules\Payment\Models;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Jet\Models\AppRepository;
use Jet\Models\Society;

/**
 * Class PaymentRepository
 * @package Jet\Modules\Payment\Models
 */
class PaymentRepository extends AppRepository
{

    /**
     * @param $start
     * @param $max
     * @param array $params
     * @return array
     */
    public function listAll($start, $max, $params = [])
    {

        $countSearch = false;
        $hasWebsite = (isset($params['website']) && !is_null($params['website']));

        /* Add DateFormat support for dql */
        $config = Payment::em()->getConfiguration();
        $config->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $query = Payment::queryBuilder();

        $select = ($hasWebsite)
            ? ['p.id AS id', 'p.title as title', 'p.reference as reference', 'p.amount as amount', 'DATE_FORMAT(p.created_at,\'%d/%m/%Y à %Hh%i\') as created_at']
            : ['p.id AS id', 'p.title as title', 'p.reference as reference', 'p.amount as amount', 'p.currency as currency', 'DATE_FORMAT(p.created_at,\'%d/%m/%Y à %Hh%i\') as created_at', 'a.id as account_id', 'concat(a.first_name, \' \', a.last_name) as client', 'w.id as website_id', 's.name as society'];

        /* Query */
        $query->select($select)
            ->from('Jet\Modules\Payment\Models\Payment', 'p')
            ->leftJoin('p.account', 'a')
            ->leftJoin('p.website', 'w')
            ->setFirstResult($start)
            ->setMaxResults($max);

        if($hasWebsite){
            $query->where($query->expr()->eq('w.id', ':website'))
                ->setParameter('website', $params['website']);
        }else{
            $query->leftJoin('w.society', 's');
        }

        /* Search params */
        if (!empty($params['search'])) {
            $countSearch = true;
            $orX = $query->expr()->orX(
                $query->expr()->like('p.title', ':search'),
                $query->expr()->like('p.reference', ':search'),
                $query->expr()->like('p.amount', ':search')
            );
            if(!$hasWebsite){
                $orX->add($query->expr()->like('s.name', ':search'));
                $orX->add($query->expr()->like('w.expiration_date', ':search'));
            }
            $query->andWhere($orX)
                ->setParameter('search', '%' . $params['search'] . '%');
        }

        /* Order params */
        if (!empty($params['order'])) {
            $columns = ($hasWebsite)
                ? ['p.title', 'p.reference', 'p.amount', 'p.amount', 'p.created_at']
                : ['a.last_name', 's.name', 'p.title', 'p.reference', 'p.amount', 'p.amount', 'p.created_at'];
            foreach ($params['order'] as $order) {
                if (isset($columns[$order['column']])) {
                    $query->addOrderBy($columns[$order['column']], strtoupper($order['dir']));
                }
            }
        } else {
            $query->orderBy('p.id', 'DESC');
        }

        $pg = new Paginator($query);
        $data = $pg->getQuery()->getArrayResult();
        $count = ($hasWebsite)
            ? (int)Payment::where('website', $params['website'])->count()
            : (int)Payment::count();
        return ['data' => $data, 'total' => ($countSearch) ? count($data) : $count];
    }


    /**
     * @param $website
     * @return mixed
     */
    public function getSocietyDetail($website)
    {
        $query = Society::queryBuilder()
            ->select('partial s.{id,name}')
            ->addSelect('partial w.{id, domain, expiration_date}')
            ->from('Jet\Models\Society', 's')
            ->leftJoin('s.website', 'w');

        $result = $query->where($query->expr()->eq('w.id', ':id'))
            ->setParameter('id', $website)
            ->getQuery()->getArrayResult();
        return isset($result[0]) ? $result[0] : null;
    }

} 