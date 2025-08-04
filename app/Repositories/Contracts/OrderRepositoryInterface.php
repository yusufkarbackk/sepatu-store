<?

namespace App\Repositories;

interface OrderRepositoryInterface
{
    public function createTransaction(array $data);
    public function findBbyTrxIdAndPhoneNumber($bookingTrxId, $phoneNumber);
    public function saveToSession(array $data);
    public function updtateSessionData(array $data);
    public function getOrderDataFromSession();
}
