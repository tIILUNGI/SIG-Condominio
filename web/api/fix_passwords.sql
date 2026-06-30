-- Actualizar senha dos administradores: admin123
-- Hash gerado com password_hash('admin123', PASSWORD_DEFAULT) no PHP 8.x
UPDATE administrador SET senha_hash = '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK' WHERE 1=1;

-- Actualizar senha dos moradores: morador123
-- Hash gerado com password_hash('morador123', PASSWORD_DEFAULT) no PHP 8.x
UPDATE morador SET senha_hash = '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa' WHERE 1=1;
