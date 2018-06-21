CREATE SCHEMA dashboard;

CREATE TABLE dashboard.tipo_grafico (
	id_tipo_grafico int NOT NULL IDENTITY(1,1),
	nombre nvarchar(150) NOT NULL,
	codigo nvarchar(150) NOT NULL,
	activo bit,
	PRIMARY KEY (id_tipo_grafico)	
);

CREATE TABLE dashboard.box (
	id_box int NOT NULL IDENTITY(1,1),
	query TEXT,
	id_tipo_grafico INT NOT NULL,
	titulo nvarchar(150) NOT NULL,
	eje_x nvarchar(150) NOT NULL,
	eje_y nvarchar(150) NOT NULL,
	columnas TEXT,
	filtro_columna nvarchar(100),
	tipo_control_grafico nvarchar(50),
	control_grafico nvarchar(2),
	activo bit,
	PRIMARY KEY (id_box)	
);
ALTER TABLE dashboard.box ADD FOREIGN KEY (id_tipo_grafico) REFERENCES dashboard.tipo_grafico(id_tipo_grafico);


INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Barra', 'BarChart', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Area', 'AreaChart', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Columnas', 'ColumnChart', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Pie', 'PieChart', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Linea', 'LineChart', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('Tabla', 'Table', 1);
INSERT INTO dashboard.tipo_grafico (nombre, codigo, activo) VALUES('KPI', 'kpi', 1);


CREATE TABLE dashboard.dashboard (
	id_dashboard int NOT NULL IDENTITY(1,1),
	nombre nvarchar(150) NOT NULL,
	menu nvarchar(150) NOT NULL,
	id_perfil INT NOT NULL,
	id_tipo_ambito INT NOT NULL,
	icono nvarchar(100) NOT NULL,
	activo bit,
	PRIMARY KEY (id_dashboard)	
);
ALTER TABLE dashboard.dashboard ADD FOREIGN KEY (id_perfil) REFERENCES core.perfil(id_perfil);
ALTER TABLE dashboard.dashboard ADD FOREIGN KEY (id_tipo_ambito) REFERENCES core.tipo_ambito(id_tipo_ambito);


CREATE TABLE dashboard.dashboard_box (
	id_dashboard_box int NOT NULL IDENTITY(1,1),
	id_box INT NOT NULL,
	id_dashboard INT NOT NULL,
	linea INT NOT NULL,
	columna INT NOT NULL,
	activo bit,
	PRIMARY KEY (id_dashboard_box)	
);
ALTER TABLE dashboard.dashboard_box ADD FOREIGN KEY (id_box) REFERENCES dashboard.box(id_box);
ALTER TABLE dashboard.dashboard_box ADD FOREIGN KEY (id_dashboard) REFERENCES dashboard.dashboard(id_dashboard);