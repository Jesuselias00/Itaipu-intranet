// src/api/funcionarios.js
// Servicio para consumir la API de funcionarios

const API_URL = "http://localhost/Itaipu-intranet/api/funcionarios";

export async function listarFuncionarios() {
  const res = await fetch(API_URL);
  const data = await res.json();
  // Si la respuesta es {status, data}, devolver solo data
  return Array.isArray(data) ? data : data.data;
}

export async function crearFuncionario(formData) {
  const res = await fetch(API_URL, {
    method: "POST",
    body: formData,
  });
  return await res.json();
}

export async function actualizarFuncionario(id, formData) {
  const res = await fetch(`${API_URL}/${id}`, {
    method: "POST", // Usamos POST + X-HTTP-Method-Override para soportar multipart
    headers: { "X-HTTP-Method-Override": "PUT" },
    body: formData,
  });
  return await res.json();
}

export async function eliminarFuncionario(id) {
  const res = await fetch(`${API_URL}/${id}`, {
    method: "DELETE",
  });
  return await res.json();
}

export async function buscarFuncionario(id) {
  const res = await fetch(`${API_URL}/${id}`);
  return await res.json();
}
