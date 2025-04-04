
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
#include "kernel/object.h"


ZEPHIR_INIT_CLASS(SafeServiceWrapper_HelloWorld)
{
	ZEPHIR_REGISTER_CLASS(SafeServiceWrapper, HelloWorld, safeservicewrapper, helloworld, safeservicewrapper_helloworld_method_entry, 0);

	return SUCCESS;
}

PHP_METHOD(SafeServiceWrapper_HelloWorld, say)
{

	php_printf("%s", "hello aa!");
}

