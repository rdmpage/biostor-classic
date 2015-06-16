# Copyright (C) 2005-2006 MySQL AB
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; version 2 of the License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

#Makefile.am example for a plugin

pkglibdir=$(libdir)/mysql
#pkglibdir=/usr/lib/mysql
INCLUDES= -I$(top_builddir)/include -I$(top_srcdir)/include -I$(includedir) -I$(includedir)/mysql
#noinst_LTLIBRARIES= mypluglib.la
pkglib_LTLIBRARIES= bi_gramlib.la
bi_gramlib_la_SOURCES= bi_gram_plugin.c
bi_gramlib_la_LDFLAGS= -module -rpath $(pkglibdir)
bi_gramlib_la_CFLAGS= -DMYSQL_DYNAMIC_PLUGIN
