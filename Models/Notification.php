<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) .'/vendor/autoload.php';

class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $user_id;
    public $ticket_id;
    public $type;
    public $message;
    public $status;
    public $created_at;

    public $title; 

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function create($user_id, $ticket_id, $type, $message, $title = 'Notification') {
        $query = "INSERT INTO " . $this->table_name . " (user_id, ticket_id, type, message, title) 
                  VALUES (:user_id, :ticket_id, :type, :message, :title)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":title", $title);
        
        if ($stmt->execute()) {
            /*if ($type === 'email') {
                $this->sendEmailNotification($user_id, $message);
            }*/
            return true;
        } else {
            return false;
        }
    }

    public function read($user_id, $status = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        if ($status !== null) {
            $query .= " AND status = :status";
        }
        
        // Add the ORDER BY clause to sort by created_t in descending order
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($status !== null) {
            $stmt->bindParam(":status", $status);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $results;
    }
    
    

    private function sendEmailNotification($user_id, $message) {
        $recipientEmail = $this->getUserEmail($user_id);
        $subject = 'New Notification';
        $body = $message;

        $mail = new PHPMailer(true); 
        $fromEmail = '';

        try {
            
            $mail->isSMTP(); 
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true; 
            $mail->Username = $fromEmail; 
            $mail->Password = ''; 
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587; 

            
            $mail->setFrom($fromEmail, 'System');
            $mail->addAddress($recipientEmail); 

            
            $mail->isHTML(true); 
            $mail->Subject = $subject;
            $mail->Body = $body;

            
            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    private function getUserEmail($user_id) {
        $query = "SELECT email FROM users WHERE id=:user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['email'];
    }
    public function countNotifications($user_id, $status) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id AND status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    public function markAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " SET status = 1 WHERE user_id = :user_id AND status = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
}




?>
