<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    // Пока заказ не отправлен, администратор может поправить получателя и адрес
    private const ADMIN_EDITABLE_STATUSES = ['new', 'paid'];

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $this->isAdmin($user) || $this->isOwner($user, $order);
    }

    /**
     * Покупатель меняет данные доставки, пока заказ новый;
     * администратор — пока заказ не передан в доставку.
     */
    public function update(User $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return in_array($order->status, self::ADMIN_EDITABLE_STATUSES, true);
        }

        return $this->isOwner($user, $order) && $order->status === 'new';
    }

    /**
     * Отменить заказ может владелец или администратор.
     * Допустим ли переход в cancelled из текущего статуса, проверяет UpdateOrderStatusService (409).
     */
    public function cancel(User $user, Order $order): bool
    {
        return $this->isAdmin($user) || $this->isOwner($user, $order);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    private function isOwner(User $user, Order $order): bool
    {
        return (int) $user->getKey() === (int) $order->user_id;
    }
}
