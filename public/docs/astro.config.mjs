import { defineConfig } from 'astro/config';
import starlight from '@astrojs/starlight';

// https://astro.build/config
export default defineConfig({
  site: 'http://localhost:8000',
  base: '/docs',
  outDir: '../docs-build',
  publicDir: './public',
  build: {
    format: 'directory',
  },
  integrations: [
    starlight({
      title: 'Gestión Afiliaciones ARL',
      logo: {
        src: '/favicon.svg',
        alt: 'Logo',
      },
      social: [
        { icon: 'github', label: 'GitHub', href: 'https://github.com/juanparen15/gestion-afiliaciones-arl' },
      ],
      sidebar: [
        {
          label: 'Inicio',
          autogenerate: { directory: 'inicio' },
        },
        {
          label: 'Instalación',
          autogenerate: { directory: 'instalacion' },
        },
        {
          label: 'Guía de Usuario',
          autogenerate: { directory: 'usuario' },
        },
        {
          label: 'Roles',
          autogenerate: { directory: 'roles' },
        },
        {
          label: 'Referencia',
          autogenerate: { directory: 'referencia' },
        },
        {
          label: 'Documentación Técnica',
          autogenerate: { directory: 'tecnica' },
        },
      ],
    }),
  ],
});
