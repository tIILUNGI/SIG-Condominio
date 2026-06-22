<?php
session_start();

// Wrapper para compatibilidade com a UI.
// A UI atual envia GET id=<id_mensalidade>.
// Este ficheiro encaminha o pedido para o backend: ../api/pagar.php

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: minhas_mensalidades.php?erro=id_invalido");
    exit;
}

// Encaminha sem alterações extras
$_GET['id'] = $id;
include("../api/pagar.php");

