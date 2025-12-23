import { defineConfig } from "prisma/config";

export default defineConfig({
  datasource: {
    url: "postgresql://postgres:coudelarialm@localhost:5432/coudelarialm",
  },
});

