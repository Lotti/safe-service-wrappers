
/* This file was generated automatically by Zephir do not modify it! */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <php.h>

#include "php_ext.h"
#include "safeservicewrapper.h"

#include <ext/standard/info.h>

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/globals.h"
#include "kernel/main.h"
#include "kernel/fcall.h"
#include "kernel/memory.h"



zend_class_entry *safeservicewrapper_helloworld_ce;
zend_class_entry *safeservicewrapper_myredis_ce;

ZEND_DECLARE_MODULE_GLOBALS(safeservicewrapper)

PHP_INI_BEGIN()
	
PHP_INI_END()

static PHP_MINIT_FUNCTION(safeservicewrapper)
{
	REGISTER_INI_ENTRIES();
	zephir_module_init();
	ZEPHIR_INIT(SafeServiceWrapper_HelloWorld);
	ZEPHIR_INIT(SafeServiceWrapper_MyRedis);
	
	return SUCCESS;
}

#ifndef ZEPHIR_RELEASE
static PHP_MSHUTDOWN_FUNCTION(safeservicewrapper)
{
	
	zephir_deinitialize_memory();
	UNREGISTER_INI_ENTRIES();
	return SUCCESS;
}
#endif

/**
 * Initialize globals on each request or each thread started
 */
static void php_zephir_init_globals(zend_safeservicewrapper_globals *safeservicewrapper_globals)
{
	safeservicewrapper_globals->initialized = 0;

	/* Cache Enabled */
	safeservicewrapper_globals->cache_enabled = 1;

	/* Recursive Lock */
	safeservicewrapper_globals->recursive_lock = 0;

	/* Static cache */
	memset(safeservicewrapper_globals->scache, '\0', sizeof(zephir_fcall_cache_entry*) * ZEPHIR_MAX_CACHE_SLOTS);

	
	
}

/**
 * Initialize globals only on each thread started
 */
static void php_zephir_init_module_globals(zend_safeservicewrapper_globals *safeservicewrapper_globals)
{
	
}

static PHP_RINIT_FUNCTION(safeservicewrapper)
{
	zend_safeservicewrapper_globals *safeservicewrapper_globals_ptr;
	safeservicewrapper_globals_ptr = ZEPHIR_VGLOBAL;

	php_zephir_init_globals(safeservicewrapper_globals_ptr);
	zephir_initialize_memory(safeservicewrapper_globals_ptr);

	
	return SUCCESS;
}

static PHP_RSHUTDOWN_FUNCTION(safeservicewrapper)
{
	
	zephir_deinitialize_memory();
	return SUCCESS;
}



static PHP_MINFO_FUNCTION(safeservicewrapper)
{
	php_info_print_box_start(0);
	php_printf("%s", PHP_SAFESERVICEWRAPPER_DESCRIPTION);
	php_info_print_box_end();

	php_info_print_table_start();
	php_info_print_table_header(2, PHP_SAFESERVICEWRAPPER_NAME, "enabled");
	php_info_print_table_row(2, "Author", PHP_SAFESERVICEWRAPPER_AUTHOR);
	php_info_print_table_row(2, "Version", PHP_SAFESERVICEWRAPPER_VERSION);
	php_info_print_table_row(2, "Build Date", __DATE__ " " __TIME__ );
	php_info_print_table_row(2, "Powered by Zephir", "Version " PHP_SAFESERVICEWRAPPER_ZEPVERSION);
	php_info_print_table_end();
	
	DISPLAY_INI_ENTRIES();
}

static PHP_GINIT_FUNCTION(safeservicewrapper)
{
#if defined(COMPILE_DL_SAFESERVICEWRAPPER) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif

	php_zephir_init_globals(safeservicewrapper_globals);
	php_zephir_init_module_globals(safeservicewrapper_globals);
}

static PHP_GSHUTDOWN_FUNCTION(safeservicewrapper)
{
	
}


zend_function_entry php_safeservicewrapper_functions[] = {
	ZEND_FE_END

};

static const zend_module_dep php_safeservicewrapper_deps[] = {
	
	ZEND_MOD_END
};

zend_module_entry safeservicewrapper_module_entry = {
	STANDARD_MODULE_HEADER_EX,
	NULL,
	php_safeservicewrapper_deps,
	PHP_SAFESERVICEWRAPPER_EXTNAME,
	php_safeservicewrapper_functions,
	PHP_MINIT(safeservicewrapper),
#ifndef ZEPHIR_RELEASE
	PHP_MSHUTDOWN(safeservicewrapper),
#else
	NULL,
#endif
	PHP_RINIT(safeservicewrapper),
	PHP_RSHUTDOWN(safeservicewrapper),
	PHP_MINFO(safeservicewrapper),
	PHP_SAFESERVICEWRAPPER_VERSION,
	ZEND_MODULE_GLOBALS(safeservicewrapper),
	PHP_GINIT(safeservicewrapper),
	PHP_GSHUTDOWN(safeservicewrapper),
#ifdef ZEPHIR_POST_REQUEST
	PHP_PRSHUTDOWN(safeservicewrapper),
#else
	NULL,
#endif
	STANDARD_MODULE_PROPERTIES_EX
};

/* implement standard "stub" routine to introduce ourselves to Zend */
#ifdef COMPILE_DL_SAFESERVICEWRAPPER
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(safeservicewrapper)
#endif
