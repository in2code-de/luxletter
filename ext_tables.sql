CREATE TABLE tx_luxletter_domain_model_newsletter (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	category int(11) DEFAULT '0' NOT NULL,
	description text NOT NULL,
	datetime int(11) DEFAULT '0' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	receivers varchar(255) DEFAULT '' NOT NULL,
	configuration int(11) DEFAULT '0' NOT NULL,
	layout varchar(255) DEFAULT '' NOT NULL,
	origin varchar(255) DEFAULT '' NOT NULL,
	bodytext mediumtext,
	disabled tinyint(4) unsigned DEFAULT '0' NOT NULL,
	language int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid),
	KEY receiver (receivers),
	KEY configuration (configuration)
);

CREATE TABLE tx_luxletter_domain_model_queue (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	email varchar(255) DEFAULT '' NOT NULL,
	newsletter int(11) DEFAULT '0' NOT NULL,
	user int(11) DEFAULT '0' NOT NULL,
	datetime int(11) DEFAULT '0' NOT NULL,
	sent tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid),
	KEY newsletter (newsletter),
	KEY user (user)
);

CREATE TABLE tx_luxletter_domain_model_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	newsletter int(11) DEFAULT '0' NOT NULL,
	user int(11) DEFAULT '0' NOT NULL,

	status tinyint(4) unsigned DEFAULT '0' NOT NULL,
	properties text,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid),
	KEY newsletter (newsletter),
	KEY user (user)
);

CREATE TABLE tx_luxletter_domain_model_link (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	newsletter int(11) DEFAULT '0' NOT NULL,
	user int(11) DEFAULT '0' NOT NULL,

	hash varchar(255) DEFAULT '' NOT NULL,
	target text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid),
	KEY hash (hash(8)),
	KEY newsletter (newsletter),
	KEY user (user)
);

CREATE TABLE tx_luxletter_domain_model_configuration (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	from_email varchar(255) DEFAULT '' NOT NULL,
	from_name varchar(255) DEFAULT '' NOT NULL,
	reply_email varchar(255) DEFAULT '' NOT NULL,
	reply_name varchar(255) DEFAULT '' NOT NULL,
	site varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE fe_users (
	luxletter_language int(11) DEFAULT '0' NOT NULL,
);

CREATE TABLE fe_groups (
	luxletter_receiver tinyint(4) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE pages (
	luxletter_subject text,
);

CREATE TABLE sys_category (
	luxletter_newsletter_category tinyint(4) unsigned DEFAULT '0' NOT NULL
);
