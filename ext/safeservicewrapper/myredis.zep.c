
#ifdef HAVE_CONFIG_H
#include "../ext_config.h"
#endif

#include <php.h>
#include "../php_ext.h"
#include "../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/fcall.h"
#include "kernel/memory.h"
#include "kernel/object.h"


ZEPHIR_INIT_CLASS(SafeServiceWrapper_MyRedis)
{
	ZEPHIR_REGISTER_CLASS_EX(SafeServiceWrapper, MyRedis, safeservicewrapper, myredis, zephir_get_internal_ce(SL("redis")), safeservicewrapper_myredis_method_entry, 0);

	return SUCCESS;
}

PHP_METHOD(SafeServiceWrapper_MyRedis, auth)
{
	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zend_long ZEPHIR_LAST_CALL_STATUS;
	zval credentials_sub, _0, _1;
	zval *credentials;

	ZVAL_UNDEF(&credentials_sub);
	ZVAL_UNDEF(&_0);
	ZVAL_UNDEF(&_1);
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_ZVAL(credentials)
	ZEND_PARSE_PARAMETERS_END();
	ZEPHIR_METHOD_GLOBALS_PTR = pecalloc(1, sizeof(zephir_method_globals), 0);
	zephir_memory_grow_stack(ZEPHIR_METHOD_GLOBALS_PTR, __func__);
	zephir_fetch_params(1, 1, 0, &credentials);
	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STRING(&_0, "SAFE_SERVICE_REDIS_PASSWORD");
	ZEPHIR_CALL_FUNCTION(&_1, "getenv", NULL, 1, &_0);
	zephir_check_call_status();
	ZEPHIR_RETURN_CALL_PARENT(safeservicewrapper_myredis_ce, getThis(), "auth", NULL, 0, &_1);
	zephir_check_call_status();
	RETURN_MM();
}

PHP_METHOD(SafeServiceWrapper_MyRedis, getAuth)
{

	RETURN_NULL();
}

