<?php
namespace App\Controller;

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
        $promotions = $this->paginate($this->Promotions);

        $this->set(compact('promotions'));
    }

    /**
     * View method
     *
     * @param string|null $id Promotion id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $promotion = $this->Promotions->get($id, [
            'contain' => ['Categories', 'Orders']
        ]);

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
            $data = $this->request->getData();
            /**
             * CakePHP todavía no soporta el formato de datos que genera datetime-local
             * Para que pueda insertar en BD, debemos eliminar la 'T' que precede a la hora
             */
            $data['available_since'] = str_replace('T', ' ', $data['available_since']);
            $data['available_until'] = str_replace('T', ' ', $data['available_until']);
            $promotion = $this->Promotions->patchEntity($promotion, $data);
            $promotion->user_id = $this->Auth->user('id');
            if ($this->Promotions->save($promotion)) {
                $this->Flash->success(__('The promotion has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The promotion could not be saved. Please, try again.'));
        }
        $categories = $this->Promotions->Categories->find('list', ['limit' => 200]);
        $this->set(compact('promotion', 'categories'));
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
            'contain' => ['Categories']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $promotion = $this->Promotions->patchEntity($promotion, $this->request->getData(), ['accesibleFields' => ['user_id' => false]]);
            if ($this->Promotions->save($promotion)) {
                $this->Flash->success(__('The promotion has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The promotion could not be saved. Please, try again.'));
        }
        $categories = $this->Promotions->Categories->find('list', ['limit' => 200]);
        $this->set(compact('promotion', 'categories'));
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
     * Permisos para usarios CON SESIÓN INICIADA
     */
    public function isAuthorized($user)
    {
        $action = $this->request->getParam('action');
        if (in_array($action, ['index', 'view'])) {
            return true;
        }
        return parent::isAuthorized($user);
    }

    /**
     * Permisos para usuarios SIN SESIÓN INICIADA
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        $this->Auth->allow('index');
        parent::beforeFilter($event);
    }
}