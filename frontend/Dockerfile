FROM node:20-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .

EXPOSE 5173

# `vite preview` sirve el build de producción real, no el servidor de
# desarrollo: más representativo de cómo correría en producción.
CMD ["sh", "-c", "npm run build && npx vite preview --host 0.0.0.0 --port 5173"]
