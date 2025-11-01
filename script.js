
// Exemplo de cálculo de idade

document.addEventListener('DOMContentLoaded', () => {
  const dataNasc = document.querySelector('input[name="data_nascimento"]');
  const idade = document.querySelector('input[name="idade"]');

  if (dataNasc && idade) {
    dataNasc.addEventListener('change', () => {
      const data = new Date(dataNasc.value);
      const hoje = new Date();
      let anos = hoje.getFullYear() - data.getFullYear();
      const mes = hoje.getMonth() - data.getMonth();
      if (mes < 0 || (mes === 0 && hoje.getDate() < data.getDate())) anos--;
      idade.value = anos;
    });
  }
});
