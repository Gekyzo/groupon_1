<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Text;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $state
 * @property string|null $body
 * @property string $image
 *
 * @property \App\Model\Entity\Promotion[] $promotions
 */
class Category extends Entity
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
        'state' => true,
        'body' => true,
        'image' => true,
        'promotions' => true
    ];

    /**
     * Creo campo virtual 'slug', combinando la id y el nombre con Inflector
     */
    protected $_virtual = ['slug'];
    protected function _getSlug()
    {
        return strtolower(Text::slug($this->name));
    }
}
