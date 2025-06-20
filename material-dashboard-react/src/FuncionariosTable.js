import React, { useEffect, useState } from "react";
import { listarFuncionarios } from "./api/funcionarios";
import Card from "@mui/material/Card";
import CardContent from "@mui/material/CardContent";
import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Avatar from "@mui/material/Avatar";
import Typography from "@mui/material/Typography";
import Button from "@mui/material/Button";
import Chip from "@mui/material/Chip";

export default function FuncionariosTable() {
  const [funcionarios, setFuncionarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchData() {
      setLoading(true);
      try {
        const data = await listarFuncionarios();
        setFuncionarios(data);
      } catch (err) {
        setError("Error al cargar funcionarios");
      }
      setLoading(false);
    }
    fetchData();
  }, []);

  if (loading) return <Typography>Cargando...</Typography>;
  if (error) return <Typography color="error">{error}</Typography>;

  return (
    <Card sx={{ maxWidth: 1200, margin: "40px auto", boxShadow: 3 }}>
      <CardContent>
        <Typography
          variant="h5"
          sx={{
            mb: 3,
            fontWeight: 700,            background: "linear-gradient(90deg,#2196f3,#21cbf3)",
            color: "white",
            p: 2,
            borderRadius: 2,
          }}
        >
          Funcionarios Table
        </Typography>
        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>AUTHOR</TableCell>
                <TableCell>FUNCTION</TableCell>
                <TableCell>STATUS</TableCell>
                <TableCell>EMPLOYED</TableCell>
                <TableCell>ACTION</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {funcionarios.map((f) => (
                <TableRow key={f.id_funcionario}>
                  <TableCell>
                    <div
                      style={{
                        display: "flex",
                        alignItems: "center",
                        gap: 12,
                      }}
                    >
                      <Avatar
                        src={f.foto_base64}
                        alt={f.nome}
                        sx={{ width: 44, height: 44, mr: 1 }}
                      />
                      <div>
                        <Typography fontWeight={600}>
                          {f.nome} {f.sobrenome}
                        </Typography>                        <Typography variant="body2" color="text.secondary">
                          {f.email}
                        </Typography>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Typography fontWeight={600}>{f.cargo}</Typography>                    <Typography variant="body2" color="text.secondary">
                      {f.nome_departamento}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Chip
                      label={f.ativo === 1 ? "ONLINE" : "OFFLINE"}
                      color={f.ativo === 1 ? "success" : "default"}
                      size="small"
                      sx={{ fontWeight: 700 }}
                    />
                  </TableCell>                  <TableCell>
                    {f.data_contratacao ? new Date(f.data_contratacao).toLocaleDateString() : "-"}
                  </TableCell>
                  <TableCell>
                    <Button variant="text" size="small">
                      Edit
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </CardContent>
    </Card>
  );
}
