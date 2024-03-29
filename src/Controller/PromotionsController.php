<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Utility\Text;
use App\Controller\AppController;

/**
 * Promotions Controller
 *
 * @property \App\Model\Table\PromotionsTable $Promotions
 *
 * @method \App\Model\Entity\Promotion[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PromotionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $promotions = $this->paginate($this->Promotions, ['contain' => ['Images']]);

        $this->set(compact('promotions'));
    }

    /**
     * View method
     *
     * @param string|null $slug Promotion slug.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($slug)
    {
        $id = $this->getIdFromUrl($slug);

        $promotion = $this->Promotions->find('all', [
            'conditions' => [
                'Promotions.id =' => $id
            ],
            'contain' => ['Categories', 'Images']
        ])->first();

        $this->set('promotion', $promotion);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $promotion = $this->Promotions->newEntity();
        if ($this->request->is('post')) {

            /* Recojo los datos */
            $this->loadComponent('Images');
            $data = $this->request->getData();

            /* Cambio el nombre de las imagenes para que sea el mismo que el de la promoción + identificador */
            foreach ($data['images'] as $key => &$promotionImage) {
                $fileExtension = substr($promotionImage['type'], (strpos($promotionImage['type'], '/') + 1));
                $promotionImage['name'] = $key . '-' . strtolower(Text::slug($data['name'])) . '.' . $fileExtension;
            }
            $files = $data['images'];

            /* Guardo las imagenes de la promoción */
            $this->Images->mainUpload('Promotion', $files);

            /**
             * Fix datetime-local format
             */
            $data['available_since'] = parent::convertDatetime($data['available_since']);
            $data['available_until'] = parent::convertDatetime($data['available_until']);
            /**
             * Asigno valor al atributo 'path' de las imágenes para que las pueda guardar en la BD.
             * Paso la variable '$imagen' por referencia con el prefijo '&'
             */
            foreach ($data['images'] as &$imagen) {
                $imagen['path'] = 'promotions/' . $imagen['name'];
            }
            $promotion = $this->Promotions->patchEntity($promotion, $data);
            if ($this->Promotions->save($promotion)) {
                $this->Flash->success(__('La promoción ha sido añadida.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The promotion could not be saved. Please, try again.'));
        }
        $categories = $this->Promotions->Categories->find('list', ['limit' => 200]);
        $images = $this->Promotions->Images->find('list', ['limit' => 200]);
        $this->set(compact('promotion', 'categories', 'images'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Promotion id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $promotion = $this->Promotions->get($id, [
            'contain' => ['Categories', 'Images']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $promotion = $this->Promotions->patchEntity($promotion, $this->request->getData());
            if ($this->Promotions->save($promotion)) {
                $this->Flash->success(__('La promoción ha sido actualizada.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The promotion could not be saved. Please, try again.'));
        }
        $categories = $this->Promotions->Categories->find('list', ['limit' => 200]);
        $images = $this->Promotions->Images->find('list', ['limit' => 200]);
        $this->set(compact('promotion', 'categories', 'images'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Promotion id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $promotion = $this->Promotions->get($id);
        if ($this->Promotions->delete($promotion)) {
            $this->Flash->success(__('The promotion has been deleted.'));
        } else {
            $this->Flash->error(__('The promotion could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Defino permisos para cualquier visitante.
     * Incluye los UNLOGGED.
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['index', 'view']);
    }

    /**
     * Defino permisos para visitantes CON SESIÓN INICIADA.
     */
    public function isAuthorized($user)
    {
        $action = $this->request->getParam('action');
        if (in_array($action, ['addToCart'])) {
            return true;
        }

        return parent::isAuthorized($user);
    }

    /**
     * Devuelve la id para cargar la vista de una carpeta/documento
     * e.g. $url = 1-primer-documento; devuelve 1
     * @param string $url La ruta de la página actual.
     * @return id
     */
    public function getIdFromUrl($url)
    {
        $length = strpos($url, '-') ? strpos($url, '-') : strlen($url);
        return substr($url, 0, $length);
    }
}
