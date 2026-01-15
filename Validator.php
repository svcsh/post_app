<?php
/**
 * Form Validation Class
 * Provides server-side validation for user input
 */

class Validator {
    private $errors = [];

    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        if (empty($email)) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate username (3-20 characters, alphanumeric and underscore only)
     */
    public static function validateUsername($username) {
        if (empty($username)) {
            return false;
        }
        
        if (strlen($username) < 3 || strlen($username) > 20) {
            return false;
        }
        
        return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
    }

    /**
     * Validate password strength
     * Requirements: Min 8 characters, at least one uppercase, one lowercase, one digit
     */
    public static function validatePasswordStrength($password) {
        if (empty($password) || strlen($password) < 8) {
            return false;
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // At least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate post title (min 3, max 255 characters)
     */
    public static function validateTitle($title) {
        if (empty($title)) {
            return false;
        }
        
        $title = trim($title);
        return strlen($title) >= 3 && strlen($title) <= 255;
    }

    /**
     * Validate post content (min 10 characters)
     */
    public static function validateContent($content) {
        if (empty($content)) {
            return false;
        }
        
        $content = trim($content);
        return strlen($content) >= 10;
    }

    /**
     * Validate search query
     */
    public static function validateSearchQuery($search) {
        if (empty($search)) {
            return true; // Empty search is valid
        }
        
        $search = trim($search);
        return strlen($search) <= 255;
    }

    /**
     * Get error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Add error message
     */
    public function addError($message) {
        $this->errors[] = $message;
    }

    /**
     * Check if there are validation errors
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Get first error message
     */
    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : '';
    }

    /**
     * Sanitize user input (basic HTML encoding)
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate user role
     */
    public static function validateRole($role) {
        $validRoles = ['admin', 'editor', 'user'];
        return in_array($role, $validRoles);
    }
}
?>
