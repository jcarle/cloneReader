/*##############################################################################
#    ____________________________________________________________________
#   /                                                                    \
#  |               ____  __      ___          _____  /     ___    ___     |
#  |     ____       /  \/  \  ' /   \      / /      /__   /   \  /   \    |
#  |    / _  \     /   /   / / /    /  ___/  \__   /     /____/ /    /    |
#  |   / |_  /    /   /   / / /    / /   /      \ /     /      /____/     |
#  |   \____/    /   /    \/_/    /  \__/  _____/ \__/  \___/ /           |
#  |                                                         /            |
#  |                                                                      |
#  |   Copyright (c) 2007                             MindStep SCOP SARL  |
#  |   Herve Masson                                                       |
#  |                                                                      |
#  |      www.mindstep.com                              www.mjslib.com    |
#  |   info-oss@mindstep.com                           mjslib@mjslib.com  |
#   \____________________________________________________________________/
#
#  Version: 1.0.0
#
#  (Svn version: $Id: jquery.printf.js 3434 2007-08-27 09:31:20Z herve $)
#
#----------[This product is distributed under a BSD license]-----------------
#
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions
#  are met:
#
#     1. Redistributions of source code must retain the above copyright
#        notice, this list of conditions and the following disclaimer.
#
#     2. Redistributions in binary form must reproduce the above copyright
#        notice, this list of conditions and the following disclaimer in
#        the documentation and/or other materials provided with the
#        distribution.
#
#  THIS SOFTWARE IS PROVIDED BY THE MINDSTEP CORP PROJECT ``AS IS'' AND
#  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
#  THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
#  PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL MINDSTEP CORP OR CONTRIBUTORS
#  BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
#  OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
#  OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
#  BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
#  WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
#  OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
#  EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
#  The views and conclusions contained in the software and documentation
#  are those of the authors and should not be interpreted as representing
#  official policies, either expressed or implied, of MindStep Corp.
#
################################################################################
#
#﻿  This is a jQuery [jquery.com] plugin that implements printf' like functions
#﻿  (Examples and documentation at: http://mjslib.com)
#
#﻿  @author: Herve Masson
#﻿  @version: 1.0.0 (8/27/2007)
#﻿  @requires jQuery v1.1.2 or later
#﻿  
#﻿  (Based on the legacy mjslib.org framework)
#
##############################################################################*/

(function($) {

﻿  /*
﻿  **﻿  Just an equivalent of the corresponding libc function
﻿  **
﻿  **﻿  var str=jQuery.sprintf("%010d %-10s",intvalue,strvalue);
﻿  **
﻿  */

﻿  $.sprintf=function(fmt)
﻿  {
﻿  ﻿  return _sprintf_(fmt,arguments,1);
﻿  }


﻿  /*
﻿  **﻿  vsprintf takes an argument list instead of a list of arguments (duh!)
﻿  **﻿  (useful when forwarding parameters from one of your functions to a printf call)
﻿  **
﻿  **﻿  str=jQuery.vsprintf(parameters[,offset]);
﻿  **
﻿  **﻿  ﻿  The 'offset' value, when present, instructs vprintf to start at the
﻿  **﻿  ﻿  corresponding index in the parameter list instead, of 0
﻿  **
﻿  **﻿  Example 1:
﻿  **
﻿  **﻿  ﻿  function myprintf(<printf like arguments>)
﻿  **﻿  ﻿  {
﻿  **﻿  ﻿  ﻿  var str=jQuery.vsprintf(arguments);
﻿  **﻿  ﻿  ﻿  ..
﻿  **﻿  ﻿  }
﻿  **﻿  ﻿  myprintf("illegal value : %s",somevalue);
﻿  **
﻿  **
﻿  **﻿  Example 2:
﻿  **
﻿  **﻿  ﻿  function logit(level,<the rest is printf like arguments>)
﻿  **﻿  ﻿  {
﻿  **﻿  ﻿  ﻿  var str=jQuery.vsprintf(arguments,1);﻿  // Skip prm #1
﻿  **﻿  ﻿  ﻿  ..
﻿  **﻿  ﻿  }
﻿  **﻿  ﻿  logit("error","illegal value : %s",somevalue);
﻿  **
﻿  */

﻿  $.vsprintf=function(args,offset)
﻿  {
﻿  ﻿  if(offset === undefined)
﻿  ﻿  {
﻿  ﻿  ﻿  offset=0;
﻿  ﻿  }
﻿  ﻿  return _sprintf_(args[offset],args,offset+1);
﻿  }


﻿  /*
﻿  **﻿  logging using formatted messages
﻿  **﻿  ================================
﻿  **
﻿  **﻿  If you _hate_ debugging with alert() as much as I do, you might find the
﻿  **﻿  following routines valuable.
﻿  **
﻿  **﻿  jQuery.alertf("The variable 'str' contains: '%s'",str);
﻿  **﻿  ﻿  Show an alert message with a printf-like argument.
﻿  **
﻿  **﻿  jQuery.logf("This is a log message, time is: %d",(new Date()).getTime());
﻿  **﻿  ﻿  Log the message on the console with the info level
﻿  **
﻿  **﻿  jQuery.errorf("The given value (%d) is erroneous",avalue);
﻿  **﻿  ﻿  Log the message on the console with the error level
﻿  **
﻿  */

﻿  $.alertf=function()
﻿  {
﻿  ﻿  return alert($.vsprintf(arguments));
﻿  }

﻿  $.vlogf=function(args)
﻿  {
﻿  ﻿  if("console" in window)
﻿  ﻿  {
﻿  ﻿  ﻿  console.info($.vsprintf(args));
﻿  ﻿  }
﻿  }

﻿  $.verrorf=function(args)
﻿  {
﻿  ﻿  if("console" in window)
﻿  ﻿  {
﻿  ﻿  ﻿  console.error($.vsprintf(args));
﻿  ﻿  }
﻿  }

﻿  $.errorf=function()
﻿  {
﻿  ﻿  $.verrorf(arguments);
﻿  }

﻿  $.logf=function()
﻿  {
﻿  ﻿  $.vlogf(arguments);
﻿  }


﻿  /*-------------------------------------------------------------------------------------------
﻿  **
﻿  **﻿  Following code is private; don't use it directly !
﻿  **
﻿  **-----------------------------------------------------------------------------------------*/

﻿  FREGEXP﻿  = /^([^%]*)%([-+])?(0)?(\d+)?(\.(\d+))?([doxXcsf])(.*)$/;
﻿  HDIGITS﻿  = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];

﻿  function _empty(str)
﻿  {
﻿  ﻿  if(str===undefined || str===null)
﻿  ﻿  {
﻿  ﻿  ﻿  return true;
﻿  ﻿  }
﻿  ﻿  return (str == "") ? true : false;
﻿  }

﻿  function _int_(val)
﻿  {
﻿  ﻿  return Math.floor(val);
﻿  }

﻿  function _printf_num_(val,base,pad,sign,width)
﻿  {
﻿  ﻿  val=parseInt(val,10);
﻿  ﻿  if(isNaN(val))
﻿  ﻿  {
﻿  ﻿  ﻿  return "NaN";
﻿  ﻿  }
﻿  ﻿  aval=(val<0)?-val:val;
﻿  ﻿  var ret="";

﻿  ﻿  if(aval==0)
﻿  ﻿  {
﻿  ﻿  ﻿  ret="0";
﻿  ﻿  }
﻿  ﻿  else
﻿  ﻿  {
﻿  ﻿  ﻿  while(aval>0)
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ret=HDIGITS[aval%base]+ret;
﻿  ﻿  ﻿  ﻿  aval=_int_(aval/base);
﻿  ﻿  ﻿  }
﻿  ﻿  }
﻿  ﻿  if(val<0)
﻿  ﻿  {
﻿  ﻿  ﻿  ret="-"+ret;
﻿  ﻿  }
﻿  ﻿  if(sign=="-")
﻿  ﻿  {
﻿  ﻿  ﻿  pad=" ";
﻿  ﻿  }
﻿  ﻿  return _printf_str_(ret,pad,sign,width,-1);
﻿  }

﻿  function _printf_float_(val,base,pad,sign,prec)
﻿  {
﻿  ﻿  if(prec==undefined)
﻿  ﻿  {
﻿  ﻿  ﻿  if(parseInt(val) != val)
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  // No decimal part and no precision -> use int formatting
﻿  ﻿  ﻿  ﻿  return ""+val;
﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  prec=5;
﻿  ﻿  }

﻿  ﻿  var p10=Math.pow(10,prec);
﻿  ﻿  var ival=""+Math.round(val*p10);
﻿  ﻿  var ilen=ival.length-prec;
﻿  ﻿  if(ilen==0)
﻿  ﻿  {
﻿  ﻿  ﻿  return "0."+ival.substr(ilen,prec);
﻿  ﻿  }
﻿  ﻿  return ival.substr(0,ilen)+"."+ival.substr(ilen,prec);
﻿  }

﻿  function _printf_str_(val,pad,sign,width,prec)
﻿  {
﻿  ﻿  var npad;

﻿  ﻿  if(val === undefined)
﻿  ﻿  {
﻿  ﻿  ﻿  return "(undefined)";
﻿  ﻿  }
﻿  ﻿  if(val === null)
﻿  ﻿  {
﻿  ﻿  ﻿  return "(null)";
﻿  ﻿  }
﻿  ﻿  if((npad=width-val.length)>0)
﻿  ﻿  {
﻿  ﻿  ﻿  if(sign=="-")
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  while(npad>0)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  val+=pad;
﻿  ﻿  ﻿  ﻿  ﻿  npad--;
﻿  ﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  else
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  while(npad>0)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  val=pad+val;
﻿  ﻿  ﻿  ﻿  ﻿  npad--;
﻿  ﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  }
﻿  ﻿  }
﻿  ﻿  if(prec>0)
﻿  ﻿  {
﻿  ﻿  ﻿  return val.substr(0,prec);
﻿  ﻿  }
﻿  ﻿  return val;
﻿  }

﻿  function _sprintf_(fmt,av,index)
﻿  {
﻿  ﻿  var output="";
﻿  ﻿  var i,m,line,match;

﻿  ﻿  line=fmt.split("\n");
﻿  ﻿  for(i=0;i<line.length;i++)
﻿  ﻿  {
﻿  ﻿  ﻿  if(i>0)
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  output+="\n";
﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  fmt=line[i];
﻿  ﻿  ﻿  while(match=FREGEXP.exec(fmt))
﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  var sign="";
﻿  ﻿  ﻿  ﻿  var pad=" ";

﻿  ﻿  ﻿  ﻿  if(!_empty(match[1])) // the left part
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  // You can't add this blindly because mozilla set the value to <undefined> when
﻿  ﻿  ﻿  ﻿  ﻿  // there is no match, and we don't want the "undefined" string be returned !
﻿  ﻿  ﻿  ﻿  ﻿  output+=match[1];
﻿  ﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  ﻿  if(!_empty(match[2])) // the sign (like in %-15s)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  sign=match[2];
﻿  ﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  ﻿  if(!_empty(match[3])) // the "0" char for padding (like in %03d)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  pad="0";
﻿  ﻿  ﻿  ﻿  }

﻿  ﻿  ﻿  ﻿  var width=match[4];﻿  // the with (32 in %032d)
﻿  ﻿  ﻿  ﻿  var prec=match[6];﻿  // the precision (10 in %.10s)
﻿  ﻿  ﻿  ﻿  var type=match[7];﻿  // the parameter type

﻿  ﻿  ﻿  ﻿  fmt=match[8];

﻿  ﻿  ﻿  ﻿  if(index>=av.length)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  ﻿  output += "[missing parameter for type '"+type+"']";
﻿  ﻿  ﻿  ﻿  ﻿  continue;
﻿  ﻿  ﻿  ﻿  }

﻿  ﻿  ﻿  ﻿  var val=av[index++];

﻿  ﻿  ﻿  ﻿  switch(type)
﻿  ﻿  ﻿  ﻿  {
﻿  ﻿  ﻿  ﻿  case "d":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_num_(val,10,pad,sign,width);
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "o":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_num_(val,8,pad,sign,width);
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "x":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_num_(val,16,pad,sign,width);
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "X":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_num_(val,16,pad,sign,width).toUpperCase();
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "c":
﻿  ﻿  ﻿  ﻿  ﻿  output += String.fromCharCode(parseInt(val,10));
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "s":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_str_(val,pad,sign,width,prec);
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  case "f":
﻿  ﻿  ﻿  ﻿  ﻿  output += _printf_float_(val,pad,sign,width,prec);
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  default:
﻿  ﻿  ﻿  ﻿  ﻿  output += "[unknown format '"+type+"']";
﻿  ﻿  ﻿  ﻿  ﻿  break;
﻿  ﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  }
﻿  ﻿  ﻿  output+=fmt;
﻿  ﻿  }
﻿  ﻿  return output;
﻿  }

})(jQuery);


