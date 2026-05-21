<?php

class ColombiaValidator {
    /**
     * Valida una cédula de ciudadanía colombiana
     * 
     * @param string $cedula Número de cédula a validar
     * @return array ['isValid' => bool, 'message' => string]
     */
    public static function validarCedula($cedula) {
        // Eliminar espacios y caracteres no numéricos
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        
        // Validar que tenga exactamente 10 dígitos
        if (strlen($cedula) !== 10) {
            return [
                'isValid' => false,
                'message' => 'La cédula debe tener exactamente 10 dígitos.'
            ];
        }
        
        // Validar que solo contenga números
        if (!ctype_digit($cedula)) {
            return [
                'isValid' => false,
                'message' => 'La cédula solo puede contener números.'
            ];
        }
        
        // La cédula ya fue validada para tener exactamente 10 dígitos numéricos
        return [
            'isValid' => true,
            'message' => 'Cédula válida.'
        ];
    }
    
    /**
     * Verifica si una cédula ya existe en la base de datos
     * 
     * @param string $cedula Número de cédula a verificar
     * @param int $excludeId (opcional) ID de usuario a excluir de la búsqueda
     * @return bool True si la cédula ya existe, False en caso contrario
     */
    public static function cedulaExiste($cedula, $excludeId = null) {
        require_once __DIR__ . "/../models/UserModel.php";
        
        if ($excludeId !== null) {
            $user = UserModel::findByCedulaExcludingId($cedula, $excludeId);
        } else {
            $user = UserModel::findByCedula($cedula);
        }
        
        return $user !== false;
    }
}
