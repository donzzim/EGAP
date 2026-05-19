import { PedidosCarrinhoScreen } from '@/components/pedidos/pedidos-carrinho-screen';

export default function PedidosPermanentesScreen() {
  return (
    <PedidosCarrinhoScreen
      tipo="permanente"
      title="Bens Permanentes"
      subtitle="Materiais patrimoniais com justificativa por item"
      icon="inventory-2"
      accentColor="#2F855A"
      currentRoute="/pedidos/permanentes"
      summaryTitle="Carrinho de permanentes"
      helperText="Itens permanentes poderao indicar adicao ou substituicao."
    />
  );
}
