<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Orders Controller
 *
 * @property \App\Model\Table\OrdersTable $Orders
 *
 * @method \App\Model\Entity\Order[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Promotions', 'Users']
        ];
        $orders = $this->paginate($this->Orders, ['order' => ['Orders.id' => 'DESC']]);

        $this->set(compact('orders'));
    }

    public function discard()
    {
        $this->Flash->error(__('Tu pedido ha sido cancelado.'));
        $this->redirect(['controller' => 'pages', 'action' => 'home']);
    }

    /**
     * Muestra la información del pedido que recoja como parámetro por URL.
     * Sólo permite ver la información de un pedido para el usuario que lo realiza.
     * Los usuarios con rol 'admin' también pueden ver cualquier pedido.     
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $order = $this->Orders->get($id, [
            'contain' => [
                'Promotions', 'Users'
            ]
        ]);

        /**
         * Sólo permito que un usuario pueda ver sus propios pedidos.
         */
        $loggedUser = $this->viewVars['currentUser'];
        if ($loggedUser['role'] !== 'admin' && $loggedUser['id'] !== $order->user_id) {
            $this->Flash->error(__('No tiene permisos.'));
            $this->redirect(['controller' => 'users', 'action' => 'profile']);
        }

        $this->set(compact('order'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $order = $this->Orders->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $order = $this->Orders->patchEntity($order, $data);
            if ($this->Orders->save($order)) {
                $this->Flash->success(__('Gracias por realizar tu pedido.'));

                return $this->redirect(['controller' => 'pages', 'action' => 'index']);
            }
            $this->Flash->error(__('No ha sido posible realizar el pedido.'));
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function confirm($promotionId)
    {
        /**
         * Cargo el modelo de Promociones y mando únicamente los campos
         * que me interesa utilizar en la vista
         */
        $this->loadModel('Promotions');
        $promotion = $this->Promotions->get($promotionId, [
            'fields' => ['id', 'name', 'price_old', 'price_new']
        ]);
        $promotion['saving'] = $promotion['price_old'] - $promotion['price_new'];
        $promotion = $this->depure($promotion, ['id', 'name', 'price_old', 'price_new', 'saving']);
        $order = $this->Orders->newEntity();
        $this->set(compact('order', 'promotion'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $order = $this->Orders->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());
            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The order could not be saved. Please, try again.'));
        }
        $promotions = $this->Orders->Promotions->find('list', ['limit' => 200]);
        $users = $this->Orders->Users->find('list', ['limit' => 200]);
        $this->set(compact('order', 'promotions', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $order = $this->Orders->get($id);
        if ($this->Orders->delete($order)) {
            $this->Flash->success(__('The order has been deleted.'));
        } else {
            $this->Flash->error(__('The order could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Defino permisos para visitantes CON SESIÓN INICIADA.
     */
    public function isAuthorized($user)
    {
        $action = $this->request->getParam('action');

        if (in_array($action, ['confirm', 'add', 'view'])) {
            return true;
        }

        return parent::isAuthorized($user);
    }
}
