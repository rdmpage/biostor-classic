/* Copyright (C) 2007 Ado Nishimura & MySQL AB

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU Lesser General Public License as published by
   the Free Software Foundation;

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA */

#include <stdlib.h>
#include <stdio.h>
#include <ctype.h>

#include <plugin.h>

#undef PACKAGE
#undef PACKAGE_NAME
#undef PACKAGE_STRING
#undef PACKAGE_TARNAME
#undef PACKAGE_VERSION
#undef VERSION

#include <mysql/my_global.h>
#include <mysql/m_ctype.h>

#if !defined(__attribute__) && (defined(__cplusplus) || !defined(__GNUC__)  || __GNUC__ == 2 && __GNUC_MINOR__ < 8)
#define __attribute__(A)
#endif

static long number_of_calls= 0; /* for SHOW STATUS, see below */

/*
  simple n-gram/bi-gram full-text parser plugin.
  - uses bi-gram to make full-text index and search string.
  - can be used for no-word-delimiter language like Chinese or Japanese.
  - can match middle of word (match 'plugins' by 'in').
  - support multi-byte string include UTF-8.
  - whitespace characters are ignored.

  NOTE: be sure to set ft_min_words=1 in 'my.cnf'.
*/

/*
  bi_gram_parser interface functions:

  Plugin declaration functions:
  - bi_gram_parser_plugin_init()
  - bi_gram_parser_plugin_deinit()

  Parser descriptor functions:
  - bi_gram_parser_parse()
  - bi_gram_parser_init()
  - bi_gram_parser_deinit()
*/


/*
  Initialize the parser plugin at server start or plugin installation.

  SYNOPSIS
    bi_gram_parser_plugin_init()

  DESCRIPTION
    Does nothing.

  RETURN VALUE
    0                    success
    1                    failure (cannot happen)
*/

static int bi_gram_parser_plugin_init(void *arg __attribute__((unused)))
{
  return(0);
}


/*
  Terminate the parser plugin at server shutdown or plugin deinstallation.

  SYNOPSIS
    bi_gram_parser_plugin_deinit()
    Does nothing.

  RETURN VALUE
    0                    success
    1                    failure (cannot happen)

*/

static int bi_gram_parser_plugin_deinit(void *arg __attribute__((unused)))
{
  return(0);
}


/*
  Initialize the parser on the first use in the query

  SYNOPSIS
    bi_gram_parser_init()

  DESCRIPTION
    Does nothing.

  RETURN VALUE
    0                    success
    1                    failure (cannot happen)
*/

static int bi_gram_parser_init(MYSQL_FTPARSER_PARAM *param
                              __attribute__((unused)))
{
    /* for debug.
    fputs("bi_gram_parser_init().\n", stderr);
    fflush(stderr); */
    return(0);
}


/*
  Terminate the parser at the end of the query

  SYNOPSIS
    bi_gram_parser_deinit()

  DESCRIPTION
    Does nothing.

  RETURN VALUE
    0                    success
    1                    failure (cannot happen)
*/

static int bi_gram_parser_deinit(MYSQL_FTPARSER_PARAM *param
                                __attribute__((unused)))
{
  return(0);
}


/*
  Pass a word back to the server.

  SYNOPSIS
    add_word()
      param              parsing context of the plugin
      word               a word
      len                word length

  DESCRIPTION
    Fill in boolean metadata for the word (if parsing in boolean mode)
    and pass the word to the server.  The server adds the word to
    a full-text index when parsing for indexing, or adds the word to
    the list of search terms when parsing a search string.
*/

static void add_word(MYSQL_FTPARSER_PARAM *param, char *word, size_t len, bool is_one_char)
{
  MYSQL_FTPARSER_BOOLEAN_INFO bool_info=
    { FT_TOKEN_WORD, 1, 0, 0, 0, ' ', 0 };

  bool_info.trunc = is_one_char;	/* search by one char. (will be ignored when indexing.)*/
  param->mysql_add_word(param, word, len, &bool_info);

/* for debug.
  fputs("'", stderr);
  fwrite(word, len, 1, stderr); 
  fputs("'\n", stderr);
*/
}

/*
  Parse a document or a search query.

  SYNOPSIS
    bi_gram_parser_parse()
      param              parsing context

  DESCRIPTION
    This is the main plugin function which is called to parse
    a document or a search query. The call mode is set in
    param->mode.  This function simply splits the text into words
    and passes every word to the MySQL full-text indexing engine.
*/

static int bi_gram_parser_parse(MYSQL_FTPARSER_PARAM *param)
{
  uchar *start, *end, *next, *docend= param->doc + param->length;
  CHARSET_INFO *cs = param->cs;
  int (*mbcharlen)(struct charset_info_st *, uint) = cs->cset->mbcharlen;
  bool is_prev_space = TRUE;

  number_of_calls++;
  start = param->doc;

  while (start < docend)
  {
    /* (is_prev_space)    start               next                 end  */
    /*        |                                                         */ 
    /*        V             |      char1       |       char2        |   */
    /*    -----------------------------------------------------------   */

    /* find 'next' char pos. */
    next = start + (*mbcharlen)(cs, *start);
    if ((next >= docend) || (*next == 0x00)) {
        if (start < next)
            add_word(param, start, next - start, TRUE);	/* just 1 char. */
        return(0);
    }
    if (start == next)		/* just in case. */
      next = start +1;

    /* skip if 1st char is space. */
    if (my_isspace(cs, *start))
    {
      start = next;
      is_prev_space = TRUE;
      continue;
    }

    /* find 'end' char pos. */
    end = next + (*mbcharlen)(cs, *next);
    if (end -1 >= docend)
      return(0);
    if (end == next)		/* just in case. */
      end = next +1;

    if (my_isspace(cs, *next))
    {
      if (is_prev_space)
        add_word(param, start, next - start, TRUE);	/* add 1 char. */
      start = end;		/* skip(+2char) if 2nd char is space. */
      is_prev_space = TRUE;
      continue;
    }

    /* pass a word to MySQL. */
    add_word(param, start, end - start, FALSE);

    start = next;
    is_prev_space = FALSE;
  }
  return(0);
}

/*
  Plugin type-specific descriptor
*/

static struct st_mysql_ftparser bi_gram_parser_descriptor=
{
  MYSQL_FTPARSER_INTERFACE_VERSION, /* interface version      */
  bi_gram_parser_parse,              /* parsing function       */
  bi_gram_parser_init,               /* parser init function   */
  bi_gram_parser_deinit              /* parser deinit function */
};

/*
  Plugin status variables for SHOW STATUS
*/

static struct st_mysql_show_var bi_gram_status[]=
{
  {"called",     (char *)&number_of_calls, SHOW_LONG},
  {0,0,0}
};

/*
  Plugin library descriptor
*/

mysql_declare_plugin(ftexample)
{
  MYSQL_FTPARSER_PLUGIN,      /* type                            */
  &bi_gram_parser_descriptor,  /* descriptor                      */
  "bi_gram",                   /* name                            */
  "Ado Nishimura",            /* author                          */
  "Simple Bi-Gram Full-Text Parser",  /* description                     */
  PLUGIN_LICENSE_GPL,
  bi_gram_parser_plugin_init,  /* init function (when loaded)     */
  bi_gram_parser_plugin_deinit,/* deinit function (when unloaded) */
  0x0001,                     /* version                         */
  bi_gram_status,              /* status variables                */
  NULL,                       /* system variables                */
  NULL                        /* config options                  */
}
mysql_declare_plugin_end;
