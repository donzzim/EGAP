import { Redirect, type Href } from 'expo-router';

export default function ConfiguracoesIndex() {
  return <Redirect href={'/configuracoes/tema' as Href} />;
}
