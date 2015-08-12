### The Idea ###
Well, these scripts represent a very simple idea for building an application in PHP. The idea is "no more than necessary". You may have heard of it somewhere.

As a daily PHP developer, I experienced these famous powerful MVC frameworks such as Symfony, CakePHP, Zend Framework. Yes, they are powerful, have everything you need and you will need. They are excellent for large scale applications. But what about a small application? Do you want to see an application package with 1~3 MB size and only 10~20KB actual code in it? OK, it's all about choice. But I don't like it. With PHP Micro Kernel, small applications always keep small.

Well there is another choice: using personal collected and organized codes to make up a small application. It's OK. But what about upgrade? Is it ready for being an application for larger scale? Maybe yes, maybe no. With PHP Micro Kernel you can keep your application in the same environment while upgrading, but not shift between bare codes and frameworks.

### What is it? ###
As the slogan saying. The Kernel only does following things.

  * Load plugins
  * Accept custom signal registration.
  * Accept signal action binding.
  * Do specific action when specific signal raised.

So you see, it does not do any actual operations. By yourself, you can make plugins doing various operations and even a framework based on it. The most important is code files will be kept well structured if you follow the structure so that they can be easily maintained.