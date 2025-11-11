# Leer antes de empezar

Este repo de github és un proyecto de segundo curso de ASIX (ASIR).

Todo el proyecto ha sido realizado con contenedores de docker para facilitar su replicación en cualquier otro dispositivo

### Clona repositorio

```bash
git clone https://github.com/mxrkkxnxn/proyecto-web-ASIX.git
```
### Iniciar contenedor de certificados
```bash
docker-compose run --rm cert-generator
```
### Iniciar resto de contenedores
```bash
docker-compose up -d --build
```
