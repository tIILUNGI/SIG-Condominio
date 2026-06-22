# SIG-Condominio — TODO

- [ ] Ler/validar como admin cria morador e como atribui casa (registar_morador vs api_atribuir_casa vs api_moradores)
- [x] Passo A: garantir consistência de ocupação/associação (morador_apartamento.activo=1 e apartamento.estado='Ocupado') em todos os endpoints
- [x] Passo B: garantir que morador criado inicia com estado_conta='Activo'

- [ ] Passo C: revisar pagamento para sincronizar estados usados no painel (pendente/atrasado/pago)
- [ ] Passo D: unificar layout/paths de CSS/JS nas páginas do morador
- [ ] Passo E: corrigir loginmorador.php para não usar fallback incorreto de senha
- [ ] Testar cenários: criar morador+atribuir apartamento; listar ocupados; pagar mensalidade e ver atualização imediata; abrir todas páginas do morador

