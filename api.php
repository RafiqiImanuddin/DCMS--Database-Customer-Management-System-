<?php
require_once __DIR__ . '/db.php';
$pdo = db();
$action = $_GET['action'] ?? 'stats';

try {
    if ($action === 'stats') {
        echo json_encode(['success' => true, 'data' => [
            'customers' => (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
            'transactions' => (int)$pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn(),
            'repeat' => (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status='repeat'")->fetchColumn(),
            'upgrade' => (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status='upgrade'")->fetchColumn(),
            'reactivate' => (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status='reactivate'")->fetchColumn(),
            'revenue' => (float)$pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM transactions')->fetchColumn()
        ]]);
    } elseif ($action === 'customers') {
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $sql = 'SELECT * FROM customers WHERE 1=1'; $params = [];
        if ($search !== '') { $sql .= ' AND (name LIKE ? OR phone LIKE ? OR customer_code LIKE ?)'; $params = array_fill(0,3,"%$search%"); }
        if ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY updated_at DESC LIMIT 100';
        $stmt=$pdo->prepare($sql); $stmt->execute($params);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    } elseif ($action === 'save_customer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $d=json_decode(file_get_contents('php://input'),true) ?: [];
        $id=(int)($d['id'] ?? 0);
        if (!$id) {
            $code='CUS-'.str_pad((string)($pdo->query('SELECT COUNT(*)+1 FROM customers')->fetchColumn()),4,'0',STR_PAD_LEFT);
            $stmt=$pdo->prepare('INSERT INTO customers (customer_code,name,phone,email,address,brand,model,sku,purchase_date,unit_price,status,next_follow_up_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$code,$d['name'],$d['phone'],$d['email']??null,$d['address']??null,$d['brand']??null,$d['model']??null,$d['sku']??null,$d['purchase_date']?:null,(float)($d['unit_price']??0),$d['status']??'prospect',$d['next_follow_up_at']?:null,$d['notes']??null]);
            echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
        } else {
            $stmt=$pdo->prepare('UPDATE customers SET name=?,phone=?,email=?,address=?,brand=?,model=?,sku=?,purchase_date=?,unit_price=?,status=?,next_follow_up_at=?,notes=? WHERE id=?');
            $stmt->execute([$d['name'],$d['phone'],$d['email']??null,$d['address']??null,$d['brand']??null,$d['model']??null,$d['sku']??null,$d['purchase_date']?:null,(float)($d['unit_price']??0),$d['status']??'prospect',$d['next_follow_up_at']?:null,$d['notes']??null,$id]);
            echo json_encode(['success'=>true,'id'=>$id]);
        }
    } elseif ($action === 'delete_customer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $d=json_decode(file_get_contents('php://input'),true) ?: [];
        $stmt=$pdo->prepare('DELETE FROM customers WHERE id=?'); $stmt->execute([(int)$d['id']]);
        echo json_encode(['success'=>true]);
    } elseif ($action === 'add_activity' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $d=json_decode(file_get_contents('php://input'),true) ?: [];
        $stmt=$pdo->prepare('INSERT INTO activities (customer_id,activity_type,title,description,scheduled_at) VALUES (?,?,?,?,?)');
        $stmt->execute([(int)$d['customer_id'],$d['activity_type']??'follow_up',$d['title'],$d['description']??null,$d['scheduled_at']?:null]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Aksi tidak ditemukan']); }
} catch (Throwable $e) { http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
