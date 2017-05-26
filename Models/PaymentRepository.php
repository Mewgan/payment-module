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

        /* Add DateFormat support for dql */
        $config = Payment::em()->getConfiguration();
        $config->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $query = Payment::queryBuilder();

        /* Query */
        $query->select(['p.id AS id', 'p.title as title', 'p.reference as reference', 'p.amount as amount', 'DATE_FORMAT(p.created_at,\'%d/%m/%Y à %Hh%i\') as created_at'])
            ->from('Jet\Modules\Payment\Models\Payment', 'p')
            ->setFirstResult($start)
            ->setMaxResults($max);

        /* Search params */
        if (!empty($params['search'])) {
            $countSearch = true;
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('p.title', ':search'),
                $query->expr()->like('p.reference', ':search'),
                $query->expr()->like('p.amount', ':search')
            ))->setParameter('search', '%' . $params['search'] . '%');
        }

        /* Order params */
        if (!empty($params['order'])) {
            $columns = ['p.id', 'p.title', 'p.reference', 'p.amount', 'p.created_at'];
            foreach ($params['order'] as $order) {
                if (isset($columns[$order['column']]))
                    $query->addOrderBy($columns[$order['column']], strtoupper($order['dir']));
            }
        } else {
            $query->orderBy('p.id', 'DESC');
        }

        $pg = new Paginator($query);
        $data = $pg->getQuery()->getArrayResult();
        return ['data' => $data, 'total' => ($countSearch) ? count($data) : (int)Payment::where('website', $params['website'])->count()];
    }


    /**
     * @param $website
     * @return mixed
     */
    public function getSocietyDetail($website)
    {
        $query = Society::queryBuilder()
            ->select('partial s.{id,name}')
            ->addSelect('partial ad.{id, address, postal_code, city, country}')
            ->addSelect('partial w.{id, domain, expiration_date}')
            ->from('Jet\Models\Society', 's')
            ->leftJoin('s.address', 'ad')
            ->join('Jet\Models\Website', 'w', Join::WITH, 's.id = w.society');

        $result = $query->where($query->expr()->eq('w.id', ':id'))
            ->setParameter('id', $website)
            ->getQuery()->getArrayResult();
        return isset($result[0]) ? $result[0] : null;
    }

} 