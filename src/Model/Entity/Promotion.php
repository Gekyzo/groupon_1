<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Promotion Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property float $price_old
 * @property float $price_new
 * @property string $body
 * @property \Cake\I18n\FrozenTime|null $available_since
 * @property \Cake\I18n\FrozenTime|null $available_until
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Order[] $orders
 * @property \App\Model\Entity\Category[] $categories
 */
class Promotion extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'slug' => true,
        'price_old' => true,
        'price_new' => true,
        'body' => true,
        'available_since' => true,
        'available_until' => true,
        'created' => true,
        'orders' => true,
        'categories' => true
    ];
}