<?php

namespace App\Models;

use App\Models\Database;

/**
 * Workspace Model
 * Handles collaborative workspaces
 */
class Workspace
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new workspace
     */
    public function create($name, $description, $ownerId)
    {
        $sql = "INSERT INTO workspaces (name, description, owner_id) VALUES (?, ?, ?)";
        $result = $this->db->query($sql, [$name, $description, $ownerId]);
        
        if ($result) {
            $workspaceId = $this->db->lastInsertId();
            // Add owner as member with owner role
            $this->addMember($workspaceId, $ownerId, 'owner');
            return ['success' => true, 'workspace_id' => $workspaceId];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la création du workspace'];
    }

    /**
     * Get workspace by ID
     */
    public function getById($workspaceId)
    {
        $sql = "SELECT w.*, u.email as owner_email, u.first_name as owner_first_name, u.last_name as owner_last_name
                FROM workspaces w
                JOIN users u ON w.owner_id = u.id
                WHERE w.id = ?";
        return $this->db->fetchOne($sql, [$workspaceId]);
    }

    /**
     * Get workspaces for a user
     */
    public function getUserWorkspaces($userId)
    {
        $sql = "SELECT DISTINCT w.*, wm.role
                FROM workspaces w
                JOIN workspace_members wm ON w.id = wm.workspace_id
                WHERE wm.user_id = ?
                ORDER BY w.created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Add member to workspace
     */
    public function addMember($workspaceId, $userId, $role = 'member')
    {
        $sql = "INSERT INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$workspaceId, $userId, $role]);
    }

    /**
     * Remove member from workspace
     */
    public function removeMember($workspaceId, $userId)
    {
        $sql = "DELETE FROM workspace_members WHERE workspace_id = ? AND user_id = ?";
        return $this->db->query($sql, [$workspaceId, $userId]);
    }

    /**
     * Update member role
     */
    public function updateMemberRole($workspaceId, $userId, $role)
    {
        $sql = "UPDATE workspace_members SET role = ? WHERE workspace_id = ? AND user_id = ?";
        return $this->db->query($sql, [$role, $workspaceId, $userId]);
    }

    /**
     * Get workspace members
     */
    public function getMembers($workspaceId)
    {
        $sql = "SELECT wm.*, u.email, u.first_name, u.last_name
                FROM workspace_members wm
                JOIN users u ON wm.user_id = u.id
                WHERE wm.workspace_id = ?
                ORDER BY wm.role DESC, u.first_name ASC";
        return $this->db->fetchAll($sql, [$workspaceId]);
    }

    /**
     * Update workspace
     */
    public function update($workspaceId, $name, $description)
    {
        $sql = "UPDATE workspaces SET name = ?, description = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$name, $description, $workspaceId]);
    }

    /**
     * Delete workspace
     */
    public function delete($workspaceId)
    {
        $sql = "DELETE FROM workspaces WHERE id = ?";
        return $this->db->query($sql, [$workspaceId]);
    }

    /**
     * Check if user is member of workspace
     */
    public function isMember($workspaceId, $userId)
    {
        $sql = "SELECT * FROM workspace_members WHERE workspace_id = ? AND user_id = ?";
        return $this->db->fetchOne($sql, [$workspaceId, $userId]) !== false;
    }

    /**
     * Check if user is owner of workspace
     */
    public function isOwner($workspaceId, $userId)
    {
        $sql = "SELECT * FROM workspaces WHERE id = ? AND owner_id = ?";
        return $this->db->fetchOne($sql, [$workspaceId, $userId]) !== false;
    }
}
