<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar vista anterior
        DB::statement('DROP VIEW IF EXISTS `vista_tareas_completas`');

        // Recrear vista con referencias a subtareas
        DB::statement("
            CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_tareas_completas` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`titulo` AS `titulo`,
                `t`.`descripcion` AS `descripcion`,
                `t`.`estado` AS `estado`,
                `t`.`prioridad` AS `prioridad`,
                `t`.`created_at` AS `fecha_creacion`,
                `t`.`fecha_completada` AS `fecha_completada`,
                `t`.`fecha_vencimiento` AS `fecha_vencimiento`,
                `t`.`usuario_id` AS `usuario_id`,
                `u`.`name` AS `usuario_nombre`,
                `u`.`email` AS `usuario_email`,
                `t`.`categoria_id` AS `categoria_id`,
                `c`.`nombre` AS `categoria_nombre`,
                `c`.`color` AS `categoria_color`,
                `c`.`icono` AS `categoria_icono`,
                (SELECT COUNT(0) FROM `subtareas` WHERE `subtareas`.`tarea_id` = `t`.`id` AND `subtareas`.`deleted_at` IS NULL) AS `total_subtareas`,
                CASE
                    WHEN `t`.`fecha_vencimiento` < CURDATE() AND `t`.`estado` = 'pendiente' THEN 'vencida'
                    WHEN `t`.`fecha_vencimiento` = CURDATE() AND `t`.`estado` = 'pendiente' THEN 'hoy'
                    WHEN `t`.`estado` = 'completada' THEN 'completada'
                    ELSE 'pendiente'
                END AS `estado_extendido`
            FROM ((`tareas` `t`
                JOIN `usuarios` `u` ON `t`.`usuario_id` = `u`.`id`)
                JOIN `categorias` `c` ON `t`.`categoria_id` = `c`.`id`)
            WHERE `t`.`deleted_at` IS NULL
                AND `u`.`deleted_at` IS NULL
                AND `c`.`deleted_at` IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar vista actualizada
        DB::statement('DROP VIEW IF EXISTS `vista_tareas_completas`');

        // Restaurar vista con referencias a comentarios
        DB::statement("
            CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_tareas_completas` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`titulo` AS `titulo`,
                `t`.`descripcion` AS `descripcion`,
                `t`.`estado` AS `estado`,
                `t`.`prioridad` AS `prioridad`,
                `t`.`created_at` AS `fecha_creacion`,
                `t`.`fecha_completada` AS `fecha_completada`,
                `t`.`fecha_vencimiento` AS `fecha_vencimiento`,
                `t`.`usuario_id` AS `usuario_id`,
                `u`.`name` AS `usuario_nombre`,
                `u`.`email` AS `usuario_email`,
                `t`.`categoria_id` AS `categoria_id`,
                `c`.`nombre` AS `categoria_nombre`,
                `c`.`color` AS `categoria_color`,
                `c`.`icono` AS `categoria_icono`,
                (SELECT COUNT(0) FROM `comentarios` WHERE `comentarios`.`tarea_id` = `t`.`id` AND `comentarios`.`deleted_at` IS NULL) AS `total_comentarios`,
                CASE
                    WHEN `t`.`fecha_vencimiento` < CURDATE() AND `t`.`estado` = 'pendiente' THEN 'vencida'
                    WHEN `t`.`fecha_vencimiento` = CURDATE() AND `t`.`estado` = 'pendiente' THEN 'hoy'
                    WHEN `t`.`estado` = 'completada' THEN 'completada'
                    ELSE 'pendiente'
                END AS `estado_extendido`
            FROM ((`tareas` `t`
                JOIN `usuarios` `u` ON `t`.`usuario_id` = `u`.`id`)
                JOIN `categorias` `c` ON `t`.`categoria_id` = `c`.`id`)
            WHERE `t`.`deleted_at` IS NULL
                AND `u`.`deleted_at` IS NULL
                AND `c`.`deleted_at` IS NULL
        ");
    }
};
