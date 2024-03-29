<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property int $id
 * @property int $promotion_id
 * @property int $user_id
 * @property string|null $state
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Promotion $promotion
 * @property \App\Model\Entity\User $user
 */
class Order extends Entity
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
        'promotion_id' => true,
        'user_id' => true,
        'state' => true,
        'created' => true,
        'promotion' => true,
        'user' => true
    ];

    /**
     * Switch accesor para el campo '$state'
     */
    protected function _getState($state)
    {
        switch ($state) {
            case 'completed':
                return 'Completado';
            default:
                return 'Pendiente';
                break;
        }
    }
}
