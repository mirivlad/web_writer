<?php
// models/User.php

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findAll() {
        $stmt = $this->pdo->prepare("SELECT id, username, display_name, email, created_at, last_login, is_active FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $is_active = $data['is_active'] ?? 0;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, display_name, email, password_hash, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['display_name'] ?? $data['username'],
            $data['email'] ?? null,
            $password_hash,
            $is_active
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET display_name = ?, email = ?";
        $params = [$data['display_name'], $data['email']];
        
        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function updateStatus($id, $is_active) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$is_active, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateLastLogin($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function updateAvatar($id, $filename) {
        $stmt = $this->pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$filename, $id]);
    }

    public function updateBio($id, $bio) {
        $stmt = $this->pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        return $stmt->execute([$bio, $id]);
    }

    public function updateProfile($id, $data) {
        $sql = "UPDATE users SET display_name = ?, email = ?, bio = ?";
        $params = [
            $data['display_name'] ?? '',
            $data['email'] ?? null,
            $data['bio'] ?? null
        ];
        
        if (!empty($data['avatar'])) {
            $sql .= ", avatar = ?";
            $params[] = $data['avatar'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
?>