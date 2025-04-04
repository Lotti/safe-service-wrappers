
/* This file was generated automatically by Zephir do not modify it! */

#ifndef PHP_SAFESERVICEWRAPPER_H
#define PHP_SAFESERVICEWRAPPER_H 1

#ifdef PHP_WIN32
#define ZEPHIR_RELEASE 1
#endif

#include "kernel/globals.h"

#define PHP_SAFESERVICEWRAPPER_NAME        "safeservicewrapper"
#define PHP_SAFESERVICEWRAPPER_VERSION     "0.0.1"
#define PHP_SAFESERVICEWRAPPER_EXTNAME     "safeservicewrapper"
#define PHP_SAFESERVICEWRAPPER_AUTHOR      "Phalcon Team"
#define PHP_SAFESERVICEWRAPPER_ZEPVERSION  "0.18.0-90070a66"
#define PHP_SAFESERVICEWRAPPER_DESCRIPTION ""



ZEND_BEGIN_MODULE_GLOBALS(safeservicewrapper)

	int initialized;

	/** Function cache */
	HashTable *fcache;

	zephir_fcall_cache_entry *scache[ZEPHIR_MAX_CACHE_SLOTS];

	/* Cache enabled */
	unsigned int cache_enabled;

	/* Max recursion control */
	unsigned int recursive_lock;

	
ZEND_END_MODULE_GLOBALS(safeservicewrapper)

#ifdef ZTS
#include "TSRM.h"
#endif

ZEND_EXTERN_MODULE_GLOBALS(safeservicewrapper)

#ifdef ZTS
	#define ZEPHIR_GLOBAL(v) ZEND_MODULE_GLOBALS_ACCESSOR(safeservicewrapper, v)
#else
	#define ZEPHIR_GLOBAL(v) (safeservicewrapper_globals.v)
#endif

#ifdef ZTS
	ZEND_TSRMLS_CACHE_EXTERN()
	#define ZEPHIR_VGLOBAL ((zend_safeservicewrapper_globals *) (*((void ***) tsrm_get_ls_cache()))[TSRM_UNSHUFFLE_RSRC_ID(safeservicewrapper_globals_id)])
#else
	#define ZEPHIR_VGLOBAL &(safeservicewrapper_globals)
#endif

#define ZEPHIR_API ZEND_API

#define zephir_globals_def safeservicewrapper_globals
#define zend_zephir_globals_def zend_safeservicewrapper_globals

extern zend_module_entry safeservicewrapper_module_entry;
#define phpext_safeservicewrapper_ptr &safeservicewrapper_module_entry

#endif
