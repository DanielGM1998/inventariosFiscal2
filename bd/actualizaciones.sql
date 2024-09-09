
-- seg_accion
CREATE TABLE `seg_accion` (
 `id` int(3) NOT NULL AUTO_INCREMENT,
 `seg_modulo_id` int(3) NOT NULL,
 `nombre` varchar(45) DEFAULT NULL,
 `url` varchar(80) DEFAULT NULL,
 `id_html` varchar(25) NOT NULL,
 `icono` varchar(40) NOT NULL DEFAULT '',
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 `estado` tinyint(1) NOT NULL DEFAULT 1,
 PRIMARY KEY (`id`) USING BTREE,
 KEY `seg_modulo_id` (`seg_modulo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `seg_accion` (`id`, `seg_modulo_id`, `nombre`, `url`, `id_html`, `icono`, `estado`) VALUES
(1, 1, 'Ver Usuarios', NULL, '', '', 1),
(2, 1, 'Agregar Usuario', NULL, '', '', 1),
(3, 1, 'Modificar Usuario', NULL, '', '', 1),
(4, 1, 'Asignar Permisos', NULL, '', '', 1),
(5, 1, 'Eliminar Usuario', NULL, '', '', 1),
(6, 1, 'Alta/Baja Usuario', NULL, '', '', 1);

-- seg_log	
CREATE TABLE `seg_log` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `usuario_id` int(6) NOT NULL,
 `seg_sesion_id` int(10) NOT NULL,
 `descripcion` varchar(255) DEFAULT NULL,
 `tabla` varchar(40) DEFAULT NULL,
 `registro` int(10) DEFAULT NULL,
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 `mostrar` tinyint(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`) USING BTREE,
 KEY `seg_sesion_id` (`seg_sesion_id`),
 KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- seg_modulo	
CREATE TABLE `seg_modulo` (
 `id` int(3) NOT NULL AUTO_INCREMENT,
 `nombre` varchar(45) NOT NULL,
 `url` varchar(80) DEFAULT NULL,
 `id_html` varchar(15) DEFAULT NULL,
 `icono` varchar(40) DEFAULT NULL,
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 `estado` tinyint(1) DEFAULT 1,
 `orden` tinyint(2) DEFAULT NULL,
 PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `seg_modulo` (`id`, `nombre`, `url`, `id_html`, `icono`, `estado`, `orden`) VALUES
(1, 'Usuarios', '/usuarios', 'usu', NULL, 1, 1),
(2, 'Proveedores', '/Proveedores', NULL, NULL, 1, NULL);

-- seg_permiso	
CREATE TABLE `seg_permiso` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `usuario_id` int(10) NOT NULL,
 `seg_accion_id` int(3) NOT NULL,
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`) USING BTREE,
 KEY `seg_accion_id` (`seg_accion_id`),
 KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- asignar permisos a usuario

-- seg_recuperar_contrasena	
CREATE TABLE `seg_recuperar_contrasena` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `usuario_id` int(6) NOT NULL,
 `ip_address` varchar(15) DEFAULT NULL,
 `user_agent` varchar(255) DEFAULT NULL,
 `codigo` varchar(8) DEFAULT NULL,
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 `estado` tinyint(1) DEFAULT NULL,
 PRIMARY KEY (`id`) USING BTREE,
 KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- seg_sesion
CREATE TABLE `seg_sesion` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `usuario_id` int(6) NOT NULL,
 `ip_address` varchar(15) DEFAULT NULL,
 `user_agent` varchar(255) DEFAULT NULL,
 `iniciada` datetime NOT NULL DEFAULT current_timestamp(),
 `finalizada` datetime DEFAULT NULL,
 `token` mediumtext DEFAULT NULL,
 `fecha_modificacion` datetime DEFAULT current_timestamp(),
 `estado` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Ultimo acceso en cat_usuarios
ALTER TABLE `cat_usuario` ADD `ultimo_acceso` DATETIME;

-- Default Estado cat_proveedor
ALTER TABLE `cat_proveedor` ALTER `estado` SET DEFAULT '1';

-- Fecha modificación cat_cliente
ALTER TABLE `cat_cliente` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación cat_comunidad
ALTER TABLE `cat_comunidad` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación cat_producto
ALTER TABLE `cat_producto` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación cat_proveedor
ALTER TABLE `cat_proveedor` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación cat_usuario
ALTER TABLE `cat_usuario` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación det_entrada
ALTER TABLE `det_entrada` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación det_entrada_comunidad
ALTER TABLE `det_entrada_comunidad` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación det_entrada_produccion
ALTER TABLE `det_entrada_produccion` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación det_entrada_tiendita
ALTER TABLE `det_entrada_tiendita` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en det_producto
ALTER TABLE `det_producto` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en det_salida
ALTER TABLE `det_salida` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en det_salida_comunidad
ALTER TABLE `det_salida_comunidad` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en det_salida_produccion
ALTER TABLE `det_salida_produccion` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en det_venta_tiendita
ALTER TABLE `det_venta_tiendita` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en entrada
ALTER TABLE `entrada` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en entrada_comunidad
ALTER TABLE `entrada_comunidad` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en entrada_produccion
ALTER TABLE `entrada_produccion` ADD `fecha_modificacion` DATETIME;

-- Ultimo acceso en entrada_tiendita
ALTER TABLE `entrada_tiendita` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación kardex
ALTER TABLE `kardex` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación kardex_comunidad
ALTER TABLE `kardex_comunidad` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación kardex_produccion
ALTER TABLE `kardex_produccion` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación kardex_tiendita
ALTER TABLE `kardex_tiendita` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación salida
ALTER TABLE `salida` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación salida_comunidad
ALTER TABLE `salida_comunidad` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación salida_produccion
ALTER TABLE `salida_produccion` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación transferencia_tiendita
ALTER TABLE `transferencia_tiendita` ADD `fecha_modificacion` DATETIME;

-- Fecha modificación venta_tiendita
ALTER TABLE `venta_tiendita` ADD `fecha_modificacion` DATETIME;

-- Default Estado cat_cliente
ALTER TABLE `cat_cliente` ALTER `estado` SET DEFAULT '1';

-- estado en det_entrada
ALTER TABLE `det_entrada` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en det_salida
ALTER TABLE `det_salida` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en entrada_comunidad
ALTER TABLE `entrada_comunidad` ALTER `estado` SET DEFAULT '1';

-- estado en entrada_produccion
ALTER TABLE `entrada_produccion` ALTER `estado` SET DEFAULT '1';

-- estado en entrada_tiendita
ALTER TABLE `entrada_tiendita` ALTER `estado` SET DEFAULT '1';

-- estado en det_entrada_comunidad
ALTER TABLE `det_entrada_comunidad` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en det_salida_comunidad
ALTER TABLE `det_salida_comunidad` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en det_entrada_produccion
ALTER TABLE `det_entrada_produccion` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en det_salida_produccion
ALTER TABLE `det_salida_produccion` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en cat_comunidad
ALTER TABLE `cat_comunidad` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en seg_permiso
-- ALTER TABLE `seg_permiso` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en kardex_comunidad
ALTER TABLE `kardex_comunidad` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en kardex
ALTER TABLE `kardex` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en kardex_produccion
ALTER TABLE `kardex_produccion` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en det_entrada_tiendita
ALTER TABLE `det_entrada_tiendita` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en kardex_tiendita
ALTER TABLE `kardex_tiendita` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en seg_log
-- ALTER TABLE `seg_log` ADD `estado` tinyint(1) DEFAULT 1;

-- estado en venta_tiendita
ALTER TABLE `venta_tiendita` ALTER `estado` SET DEFAULT '1';

-- estado en det_venta_tiendita
ALTER TABLE `det_venta_tiendita` ADD `estado` tinyint(1) DEFAULT 1;

ALTER TABLE `cat_usuario` ADD `telefono` VARCHAR(15) NOT NULL DEFAULT '' AFTER `email`;
ALTER TABLE `cat_usuario` CHANGE `email` `email` VARCHAR(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '';

UPDATE cat_usuario SET email = '' WHERE email IS NULL;
UPDATE cat_cliente SET email = '' WHERE email IS NULL;
UPDATE cat_cliente SET telefono = '' WHERE telefono IS NULL;
UPDATE cat_cliente SET direccion = '' WHERE direccion IS NULL;
UPDATE cat_proveedor SET email = '' WHERE email IS NULL;
UPDATE cat_proveedor SET telefono = '' WHERE telefono IS NULL;
UPDATE cat_proveedor SET direccion = '' WHERE direccion IS NULL;
UPDATE cat_proveedor SET rfc = '' WHERE rfc IS NULL;