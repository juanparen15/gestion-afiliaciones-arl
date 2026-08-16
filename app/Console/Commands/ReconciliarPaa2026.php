<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reconciliacion PAA 2026 (una sola vez):
 *   FASE 1  renumera los 14 registros propios del nuevo 647-660 -> 669-682 (+22).
 *   FASE 2  inserta los 22 registros OFICIALES 647-668 (del viejo/SECOP) con sus
 *           FKs, dependencia mapeada, usuario por correo e items UNSPSC.
 * Idempotente y transaccional.
 *   php artisan paa:reconciliar-2026 --dry-run   (ver que haria)
 *   php artisan paa:reconciliar-2026             (aplicar)
 */
class ReconciliarPaa2026 extends Command
{
    protected $signature = 'paa:reconciliar-2026 {--dry-run : Muestra lo que haria sin escribir}';
    protected $description = 'Reconcilia consecutivos PAA 2026 con SECOP (renumera 14 propios e importa 22 oficiales)';

    /** @var array<int,array<string,mixed>> Los 22 oficiales 647-668 (ya mapeados a la BD nueva). */
    private array $oficiales = array (
      0 => 
      array (
        'id_vigencia' => 647,
        'descripcioncont' => 'INTERVENTORÍA TÉCNICA, ADMINISTRATIVA Y FINANCIERA SOBRE EL CONTRATO DE APOYO A LA PERMANENCIA DE ALUMNOS DE ESCASOS RECURSOS AL SISTEMA EDUCATIVO DEL ÁREA RURAL CON TRANSPORTE ESCOLAR SEGUNDA FASE DE LA VIGENCIA 2026, EN EL MUNICIPIO DE PUERTO BOYACÁ, BOYACÁ',
        'valorestimadocont' => '242.267.820',
        'valorestimadovig' => '242.267.820',
        'duracont' => '6',
        'codbpim' => NULL,
        'slug' => 'interventoria-tecnica-administrativa-y-financiera-sobre-el-contrato-de-apoyo-a-la-permanencia-de-alumnos-de-escasos-recursos-al-sistema-educativo-del-area-rural-con-transporte-escolar-segunda-fase-de-la-vigencia-2026-en-el-municipio-de-puerto-boyaca-boyaca-2592',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 2,
        'estadovigencia_id' => 1,
        'modalidade_id' => 8,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 13,
        'requiproyecto_id' => 1,
        'fuente_id' => 4,
        'tipoprioridade_id' => 1,
        'mese_id' => 6,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 10,
        'user_id' => 36,
        'productos' => 
        array (
        ),
      ),
      1 => 
      array (
        'id_vigencia' => 648,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES ESPECIALIZADOS PARA ACOMPAÑAR LA ELABORACIÓN, ESTRUCTURACIÓN, REDACCIÓN Y TRÁMITE DE APROBACIÓN DEL REGLAMENTO INTERNO DE TRABAJO DE LOS TRABAJADORES OFICIALES VINCULADOS A LA ALCALDÍA MUNICIPAL DE PUERTO BOYACÁ-BOYACÁ',
        'valorestimadocont' => '35.000.000',
        'valorestimadovig' => '35.000.000',
        'duracont' => '1',
        'codbpim' => 'NA',
        'slug' => 'prestacion-de-servicios-profesionales-especializados-para-acompanar-la-elaboracion-estructuracion-redaccion-y-tramite-de-aprobacion-del-reglamento-interno-de-trabajo-de-los-trabajadores-oficiales-vinculados-a-la-alcaldia-municipal-de-puerto-boyaca-boyaca-2593',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 2,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 6,
        'requipoai_id' => 2,
        'area_id' => 8,
        'dependencia_id' => 8,
        'user_id' => 36,
        'productos' => 
        array (
        ),
      ),
      2 => 
      array (
        'id_vigencia' => 649,
        'descripcioncont' => 'ELABORAR AVALÚO COMERCIAL PARA ADQUISIÓN DE PREDIOS DE INTERES ESTRATÉGICO AMBIENTAL PARA LA CONSERVACIÓN Y PROTECCIÓN DE MICROCUENCAS EN EL ÁREA RURAL DEL MUNICIPIO DE PUERTO BOYACÁ (INCLUYE TRANSPORTE',
        'valorestimadocont' => '16.500.000',
        'valorestimadovig' => '16.500.000',
        'duracont' => '1',
        'codbpim' => NULL,
        'slug' => 'elaborar-avaluo-comercial-para-adquision-de-predios-de-interes-estrategico-ambiental-para-la-conservacion-y-proteccion-de-microcuencas-en-el-area-rural-del-municipio-de-puerto-boyaca-incluye-transporte-2594',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 2,
        'estadovigencia_id' => 1,
        'modalidade_id' => 7,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 13,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 7,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80131802,
            1 => 801318,
          ),
        ),
      ),
      3 => 
      array (
        'id_vigencia' => 650,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES COMO ARQUITECTA PARA BRINDAR APOYO EN ACTIVIDADES RELACIONADAS CON CONTROL URBANO Y ESPACIO PÚBLICO  A CARGO DE LA SECRETARIA DE PLANEACIÓN DEL MUNICIPIO DE PUERTO BOYACÁ',
        'valorestimadocont' => '24.000.000',
        'valorestimadovig' => '24.000.000',
        'duracont' => '6',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-como-arquitecta-para-brindar-apoyo-en-actividades-relacionadas-con-control-urbano-y-espacio-publico-a-cargo-de-la-secretaria-de-planeacion-del-municipio-de-puerto-boyaca-2595',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 2,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 7,
        'requipoai_id' => 2,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      4 => 
      array (
        'id_vigencia' => 651,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA BRINDAR ACOMPAÑAMIENTO ADMINISTRATIVO A LA COMISARÍA DE FAMILIA DEL MUNICIPIO DE PUERTO BOYACÁ.',
        'valorestimadocont' => '20.160.000',
        'valorestimadovig' => '20.160.000',
        'duracont' => '168',
        'codbpim' => '202500000047768',
        'slug' => 'prestacion-de-servicios-profesionales-para-brindar-acompanamiento-administrativo-a-la-comisaria-de-familia-del-municipio-de-puerto-boyaca-2596',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 3,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 1,
        'mese_id' => 7,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 9,
        'user_id' => 5,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80111620,
            1 => 801116,
          ),
        ),
      ),
      5 => 
      array (
        'id_vigencia' => 652,
        'descripcioncont' => 'ACOMPAÑAMIENTO, ASISTENCIA TÉCNICA Y ASESORÍA ESPECIALIZADA, ORIENTADA A LA EXITOSA CONSTRUCCIÓN Y ESTRUCTURACIÓN DEL PRESUPUESTO GENERAL PARA LA VIGENCIA FISCAL 2027, ALCALDIA DE    PUERTO BOYACÁ',
        'valorestimadocont' => '30.000.000',
        'valorestimadovig' => '30.000.000',
        'duracont' => '4',
        'codbpim' => 'N/A',
        'slug' => 'acompanamiento-asistencia-tecnica-y-asesoria-especializada-orientada-a-la-exitosa-construccion-y-estructuracion-del-presupuesto-general-para-la-vigencia-fiscal-2027-alcaldia-de-puerto-boyaca-2597',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 2,
        'fuente_id' => 1,
        'tipoprioridade_id' => 1,
        'mese_id' => 7,
        'requipoai_id' => 2,
        'area_id' => NULL,
        'dependencia_id' => 12,
        'user_id' => 7,
        'productos' => 
        array (
          0 => 
          array (
            0 => 93151601,
            1 => 931516,
          ),
        ),
      ),
      6 => 
      array (
        'id_vigencia' => 653,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS EN INGENIERÍA CIVIL Y/O A FINES CON ÉNFASIS EN INFRAESTRUCTURAS VERTICALES PARA LA GESTIÓN DE PROYECTOS DE INVERSIÓN PÚBLICA',
        'valorestimadocont' => '27.466.560',
        'valorestimadovig' => '27.466.560',
        'duracont' => '160',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-en-ingenieria-civil-yo-a-fines-con-enfasis-en-infraestructuras-verticales-para-la-gestion-de-proyectos-de-inversion-publica-2598',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      7 => 
      array (
        'id_vigencia' => 654,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES EN ARQUITECTURA, INGENIERÍA CIVIL Y/O A FINES, PARA APOYAR LA GESTIÓN DE PROGRAMAS DE ORDENAMIENTO TERRITORIAL',
        'valorestimadocont' => '23.174.910',
        'valorestimadovig' => '23.174.910',
        'duracont' => '135',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-en-arquitectura-ingenieria-civil-yo-a-fines-para-apoyar-la-gestion-de-programas-de-ordenamiento-territorial-2599',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      8 => 
      array (
        'id_vigencia' => 655,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES EN DERECHO Y/O A FINES PARA LA GESTIÓN DE PROYECTOS DE INVERSIÓN PÚBLICA',
        'valorestimadocont' => '23.174.910',
        'valorestimadovig' => '23.174.910',
        'duracont' => '135',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-en-derecho-yo-a-fines-para-la-gestion-de-proyectos-de-inversion-publica-2600',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      9 => 
      array (
        'id_vigencia' => 656,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA LA GESTIÓN DE APLICATIVOS WEB Y TRAZABILIDAD DE LA INVERSIÓN PÚBLICA',
        'valorestimadocont' => '27.000.000',
        'valorestimadovig' => '27.000.000',
        'duracont' => '135',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-para-la-gestion-de-aplicativos-web-y-trazabilidad-de-la-inversion-publica-2601',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      10 => 
      array (
        'id_vigencia' => 657,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA LA GESTIÓN FINANCIERA Y ECONÓMICA DE LA INVERSIÓN PÚBLICA',
        'valorestimadocont' => '24.000.000',
        'valorestimadovig' => '24.000.000',
        'duracont' => '5',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-para-la-gestion-financiera-y-economica-de-la-inversion-publica-2602',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      11 => 
      array (
        'id_vigencia' => 658,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS EN INGENIERÍA SANITARIA Y/O AMBIENTAL PARA LA GESTIÓN DE PROYECTOS DE INVERSIÓN PÚBLICA',
        'valorestimadocont' => '24.000.000',
        'valorestimadovig' => '24.000.000',
        'duracont' => '5',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-en-ingenieria-sanitaria-yo-ambiental-para-la-gestion-de-proyectos-de-inversion-publica-2603',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      12 => 
      array (
        'id_vigencia' => 659,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA LA GESTIÓN DE PROYECTOS DE INVERSIÓN PÚBLICA EN MATERIA PRECONTRACTUAL, DE EJECUCIÓN Y POSTCONTRACTUAL QUE ADELANTA LA SECRETARÍA DE PLANEACIÓN DEL MUNICIPIO DE PUERTO BOYACÁ, BOYACÁ.',
        'valorestimadocont' => '20.750.000',
        'valorestimadovig' => '20.750.000',
        'duracont' => '5',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-profesionales-para-la-gestion-de-proyectos-de-inversion-publica-en-materia-precontractual-de-ejecucion-y-postcontractual-que-adelanta-la-secretaria-de-planeacion-del-municipio-de-puerto-boyaca-boyaca-2604',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      13 => 
      array (
        'id_vigencia' => 660,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PARA APOYAR EL DESARROLLO DE LAS ESTRATEGIAS QUE ADELANTA LA SECRETARÍA DEPLANEACIÓN DE ACUERDO A SUS COMPETENCIAS EN EL ÁREA URBANA Y RURAL DEL MUNICIPIO DE PUERTO BOYACÁ',
        'valorestimadocont' => '15.750.000',
        'valorestimadovig' => '15.750.000',
        'duracont' => '5',
        'codbpim' => NULL,
        'slug' => 'prestacion-de-servicios-para-apoyar-el-desarrollo-de-las-estrategias-que-adelanta-la-secretaria-deplaneacion-de-acuerdo-a-sus-competencias-en-el-area-urbana-y-rural-del-municipio-de-puerto-boyaca-2605',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80101604,
            1 => 801016,
          ),
        ),
      ),
      14 => 
      array (
        'id_vigencia' => 661,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES COMO ADMINISTRADOR DE EMPRESAS PARA BRINDAR ACOMPAÑAMIENTO TÉCNICO Y ADMINISTRATIVO AL DESPACHO DEL ALCALDE PARA EL FORTALECIMIENTO DE LOS PROCESOS ADMINISTRATIVOS Y ESTRATÉGICOS REQUERIDOS PARA EL CUMPLIMIENTO DE LOS FINES DE LA ADMINISTRACIÓN MUNICIPAL”.',
        'valorestimadocont' => '23.250.000',
        'valorestimadovig' => '23.250.000',
        'duracont' => '5',
        'codbpim' => 'NO APLICA',
        'slug' => 'prestacion-de-servicios-profesionales-como-administrador-de-empresas-para-brindar-acompanamiento-tecnico-y-administrativo-al-despacho-del-alcalde-para-el-fortalecimiento-de-los-procesos-administrativos-y-estrategicos-requeridos-para-el-cumplimiento-de-los-fines-de-la-administracion-municipal-2606',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 2,
        'fuente_id' => 1,
        'tipoprioridade_id' => 3,
        'mese_id' => 7,
        'requipoai_id' => 2,
        'area_id' => NULL,
        'dependencia_id' => 9,
        'user_id' => 37,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80111620,
            1 => 801116,
          ),
          1 => 
          array (
            0 => 80111620,
            1 => 801116,
          ),
          2 => 
          array (
            0 => 80111620,
            1 => 801116,
          ),
        ),
      ),
      15 => 
      array (
        'id_vigencia' => 662,
        'descripcioncont' => 'REALIZAR MEDIDAS DE CONSERVACION A TRAVÉS DE MANTENIMIENTO A REFORESTACIONES PARA LA PROTECCIÓN DE ECOSISTEMAS, SOSTENIBILIDAD ECOLÓGICA Y AMBIENTAL EN EL MUNICIPIO DE PUERTO BOYACÁ”.',
        'valorestimadocont' => '165.111.211',
        'valorestimadovig' => '165.111.211',
        'duracont' => '3',
        'codbpim' => NULL,
        'slug' => 'realizar-medidas-de-conservacion-a-traves-de-mantenimiento-a-reforestaciones-para-la-proteccion-de-ecosistemas-sostenibilidad-ecologica-y-ambiental-en-el-municipio-de-puerto-boyaca-2607',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 2,
        'estadovigencia_id' => 1,
        'modalidade_id' => 2,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 13,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 7,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 11,
        'user_id' => 6,
        'productos' => 
        array (
          0 => 
          array (
            0 => 70111501,
            1 => 701115,
          ),
          1 => 
          array (
            0 => 70151802,
            1 => 701518,
          ),
        ),
      ),
      16 => 
      array (
        'id_vigencia' => 663,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS DE APOYO A LA GESTIÓN PARA BRINDAR ACOMPAÑAMIENTO EN EL DESARROLLO DE ACTIVIDADES RELACIONADAS CON EL SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO SG-SST- DE LA ALCALDÍA MUNICIPAL DE PUERTO BOYACÁ, BOYACÁ',
        'valorestimadocont' => '12.500.000',
        'valorestimadovig' => '12.500.000',
        'duracont' => '5',
        'codbpim' => 'NA',
        'slug' => 'prestacion-de-servicios-de-apoyo-a-la-gestion-para-brindar-acompanamiento-en-el-desarrollo-de-actividades-relacionadas-con-el-sistema-de-gestion-de-seguridad-y-salud-en-el-trabajo-sg-sst-de-la-alcaldia-municipal-de-puerto-boyaca-boyaca-2608',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 2,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 2,
        'area_id' => 8,
        'dependencia_id' => 8,
        'user_id' => 36,
        'productos' => 
        array (
          0 => 
          array (
            0 => 93151507,
            1 => 931515,
          ),
        ),
      ),
      17 => 
      array (
        'id_vigencia' => 664,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS DE APOYO A LA GESTIÓN EN EL ÁREA DE SISTEMAS PARA BRINDAR SOPORTE AL PORTAL WEB INSTITUCIONAL Y A LA ADMINISTRACIÓN DEL APLICATIVO GLPI, INCLUYENDO LA GESTIÓN DE CONTENIDOS, PUBLICACIÓN DE INFORMACIÓN Y APOYO A LA MESA DE AYUDA TECNOLÓGICA',
        'valorestimadocont' => '9.072.000',
        'valorestimadovig' => '9.072.000',
        'duracont' => '90',
        'codbpim' => '25-9-15-572-00031',
        'slug' => 'prestacion-de-servicios-de-apoyo-a-la-gestion-en-el-area-de-sistemas-para-brindar-soporte-al-portal-web-institucional-y-a-la-administracion-del-aplicativo-glpi-incluyendo-la-gestion-de-contenidos-publicacion-de-informacion-y-apoyo-a-la-mesa-de-ayuda-tecnologica-2609',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 1,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 2,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => 10,
        'dependencia_id' => 8,
        'user_id' => 3,
        'productos' => 
        array (
          0 => 
          array (
            0 => 81111808,
            1 => 811118,
          ),
        ),
      ),
      18 => 
      array (
        'id_vigencia' => 665,
        'descripcioncont' => 'APOYO LOGÍSTICO PARA EL DESARROLLO DE LA SEMANA DE LA JUVENTUD Y EL PRIMER ENCUENTRO JUVENIL DEL MAGDALENA MEDIO, A ADELANTARSE EN EL MUNICIPIO DE PUERTO BOYACÁ, BOYACÁ',
        'valorestimadocont' => '33.590.000',
        'valorestimadovig' => '33.590.000',
        'duracont' => '2',
        'codbpim' => '25-9-15-572-00055',
        'slug' => 'apoyo-logistico-para-el-desarrollo-de-la-semana-de-la-juventud-y-el-primer-encuentro-juvenil-del-magdalena-medio-a-adelantarse-en-el-municipio-de-puerto-boyaca-boyaca-2610',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 7,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 1,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 10,
        'user_id' => 9,
        'productos' => 
        array (
          0 => 
          array (
            0 => 81141601,
            1 => 811416,
          ),
        ),
      ),
      19 => 
      array (
        'id_vigencia' => 666,
        'descripcioncont' => 'APOYO LOGISTICO PARA LA ORGANIZACIÓN Y EJECUCIÓN DE LAS ACTIVIDADES DE FORTALECIMIENTO CULTURAL DE LA POBLACIÓN AFRODESCENDIENTE EN EL MUNICIPIO DE PUERTO BOYACÁ.',
        'valorestimadocont' => '30.000.000',
        'valorestimadovig' => '30.000.000',
        'duracont' => '1',
        'codbpim' => '202500000047982',
        'slug' => 'apoyo-logistico-para-la-organizacion-y-ejecucion-de-las-actividades-de-fortalecimiento-cultural-de-la-poblacion-afrodescendiente-en-el-municipio-de-puerto-boyaca-2611',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 3,
        'estadovigencia_id' => 1,
        'modalidade_id' => 7,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 3,
        'mese_id' => 8,
        'requipoai_id' => 1,
        'area_id' => NULL,
        'dependencia_id' => 9,
        'user_id' => 5,
        'productos' => 
        array (
          0 => 
          array (
            0 => 81141601,
            1 => 811416,
          ),
          1 => 
          array (
            0 => 90101603,
            1 => 901016,
          ),
          2 => 
          array (
            0 => 93131608,
            1 => 931316,
          ),
          3 => 
          array (
            0 => 93141701,
            1 => 931417,
          ),
        ),
      ),
      20 => 
      array (
        'id_vigencia' => 667,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA APOYAR LA GESTIÓN ADMINISTRATIVA, EL SEGUIMIENTO Y FORTALECIMIENTO DE LOS PROCESOS CATASTRALES Y TRIBUTARIOS DE LA SECRETARÍA DE HACIENDA DEL MUNICIPIO DE PUERTO BOYACÁ.',
        'valorestimadocont' => '14.400.000',
        'valorestimadovig' => '14.400.000',
        'duracont' => '4',
        'codbpim' => '202500000048369',
        'slug' => 'prestacion-de-servicios-profesionales-para-apoyar-la-gestion-administrativa-el-seguimiento-y-fortalecimiento-de-los-procesos-catastrales-y-tributarios-de-la-secretaria-de-hacienda-del-municipio-de-puerto-boyaca-2612',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 1,
        'mese_id' => 8,
        'requipoai_id' => 2,
        'area_id' => NULL,
        'dependencia_id' => 12,
        'user_id' => 7,
        'productos' => 
        array (
        ),
      ),
      21 => 
      array (
        'id_vigencia' => 668,
        'descripcioncont' => 'PRESTACIÓN DE SERVICIOS PROFESIONALES PARA APOYAR LA GESTIÓN ADMINISTRATIVA, EL SEGUIMIENTO Y FORTALECIMIENTO DE LOS PROCESOS CATASTRALES Y TRIBUTARIOS DE LA SECRETARÍA DE HACIENDA DEL MUNICIPIO DE PUERTO BOYACÁ.',
        'valorestimadocont' => '14.400.000',
        'valorestimadovig' => '14.400.000',
        'duracont' => '4',
        'codbpim' => '202500000048369',
        'slug' => 'prestacion-de-servicios-profesionales-para-apoyar-la-gestion-administrativa-el-seguimiento-y-fortalecimiento-de-los-procesos-catastrales-y-tributarios-de-la-secretaria-de-hacienda-del-municipio-de-puerto-boyaca-2613',
        'created_at' => '2025-12-31 19:00:00',
        'intervalo_id' => 2,
        'vigenfutura_id' => 2,
        'tipozona_id' => 1,
        'estadovigencia_id' => 1,
        'modalidade_id' => 6,
        'tipoproceso_id' => NULL,
        'tipoadquisicione_id' => 1,
        'requiproyecto_id' => 1,
        'fuente_id' => 1,
        'tipoprioridade_id' => 1,
        'mese_id' => 8,
        'requipoai_id' => 2,
        'area_id' => NULL,
        'dependencia_id' => 12,
        'user_id' => 7,
        'productos' => 
        array (
          0 => 
          array (
            0 => 80111605,
            1 => 801116,
          ),
        ),
      ),
    );

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $q = fn() => DB::table('planadquisiciones')->where('vigencia', 2026);
        $max      = (int) ($q()->max('id_vigencia') ?? 0);
        $en647660 = $q()->whereBetween('id_vigencia', [647, 660])->count();
        $en661682 = $q()->whereBetween('id_vigencia', [661, 682])->count();
        $en647668 = $q()->whereBetween('id_vigencia', [647, 668])->count();
        $en669682 = $q()->whereBetween('id_vigencia', [669, 682])->count();

        if ($max === 682 && $en647668 === 22 && $en669682 === 14) {
            $this->info('Ya estaba reconciliado (647-668 oficiales, 669-682 propios). No hago nada.');
            return self::SUCCESS;
        }

        if (! ($max === 660 && $en647660 === 14 && $en661682 === 0)) {
            $this->error('Estado inesperado en 2026. Se esperaba: max=660, 14 en 647-660, 0 en 661-682.');
            $this->line("  actual -> max=$max | 647-660=$en647660 | 661-682=$en661682");
            $this->warn('Aborto por seguridad. Revisa manualmente antes de continuar.');
            return self::FAILURE;
        }

        $totalProd = array_sum(array_map(fn($r) => count($r['productos']), $this->oficiales));
        $flags = array_values(array_filter(array_map(fn($r) => $r['dependencia_id'] === null ? $r['id_vigencia'] : null, $this->oficiales)));

        $this->info('Estado inicial OK. Plan:');
        $this->line('  FASE 1: renumerar 14 registros 647-660 -> 669-682 (+22)');
        $this->line("  FASE 2: insertar ".count($this->oficiales)." oficiales 647-668 + $totalProd items UNSPSC");

        if ($dry) {
            $this->warn('--dry-run: no se escribio nada.');
            $this->line('  Sin dependencia (asignar luego): '.implode(', ', $flags));
            return self::SUCCESS;
        }

        DB::transaction(function () {
            DB::table('planadquisiciones')->where('vigencia', 2026)
                ->whereBetween('id_vigencia', [647, 660])
                ->update(['id_vigencia' => DB::raw('id_vigencia + 22'), 'updated_at' => now()]);

            foreach ($this->oficiales as $rec) {
                $productos = $rec['productos'];
                unset($rec['productos']);
                $rec['vigencia']   = 2026;
                $rec['updated_at'] = now();
                $id = DB::table('planadquisiciones')->insertGetId($rec);
                foreach ($productos as [$pid, $cid]) {
                    DB::table('planadquisicione_producto')->insert([
                        'planadquisicione_id' => $id,
                        'producto_id'         => $pid,
                        'clase_id'            => $cid,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }
        });

        $f = fn($a, $b) => DB::table('planadquisiciones')->where('vigencia', 2026)->whereBetween('id_vigencia', [$a, $b])->count();
        $this->info('Hecho. Verificacion:');
        $this->line('  total 2026 = '.DB::table('planadquisiciones')->where('vigencia', 2026)->count());
        $this->line('  647-668 (oficiales) = '.$f(647, 668).'  (esperado 22)');
        $this->line('  669-682 (propios)   = '.$f(669, 682).'  (esperado 14)');
        $this->line('  max 2026 = '.DB::table('planadquisiciones')->where('vigencia', 2026)->max('id_vigencia').'  (esperado 682)');
        $this->newLine();
        $this->warn('Asigna la dependencia manualmente a estos consecutivos: '.implode(', ', $flags));

        return self::SUCCESS;
    }
}