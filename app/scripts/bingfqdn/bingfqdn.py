#!/bin/python
#
# bingfqdn.py
#
# Author: Justin C. Klein Keane
#
# Integrate HECTOR scans for web ports with a reverse IP
# based lookup via Bing.  This will show, for example, 
# what hostnames or URL's are associated with a specific
# host's IP address.  Useful for identifying web sites
# that aren't available via the default URL.  This 
# script populates the URL table that is then used for
# facilities like screen shots.
#

import MySQLdb
import urllib2
import base64
from pull_config import Configurator

configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')
DEBUG = True

APIKEY = configr.get_var('bing_api_key')
BINGURL = 'https://api.datamarket.azure.com/Bing/Search/Web?Query=%27ip%3A'
BASE64STRING = base64.encodestring('%s:%s' % (APIKEY,APIKEY)).replace('\n', '')
BASE64STRING = (':%s' % APIKEY).encode('base64')[:-1]

try:
  conn = MySQLdb.connect(host=HOST,
                      user=USERNAME,
                      passwd=PASSWORD,
                      db=DB)
except Exception as err:
  print "Error connecting to the database" , err

#look up IP's for web servers
cursor = conn.cursor()
sql = """SELECT DISTINCT(host_id) 
    FROM nmap_result
    WHERE nmap_result_port_number IN (80,443,8000,8080) 
      AND state_id = 1 """
cursor.execute(sql)
host_ids = cursor.fetchall()
cursor.close()

hostmapip = {}

lastip = ''
# Pull the IP addresses of the hosts
for host_id in host_ids:
  cursor = conn.cursor()
  sql = 'SELECT host_ip FROM host WHERE host_id = %s'
  cursor.execute(sql, (host_id[0]))
  ip = cursor.fetchone()[0]
  hostmapip[host_id[0]] = ip

# Poll Bing
for host_id, host_ip in hostmapip.iteritems():
  url = BINGURL + str(host_ip) + '%27'
  request = urllib2.Request(url)
  request.add_header("Authorization", "Basic %s" % BASE64STRING)   
  try:
    result = urllib2.urlopen(request)
    retval = result.read()
  except Exception as err:
    print "Error polling Bing ", err
  
  x = 0
  for displayurl in retval.split('<d:DisplayUrl m:type="Edm.String">'):
    if x == 0:
      x = 1 # The first result is never applicable
      pass
    else:
      x = x + 1
      urlandtag = displayurl.split('</d:DisplayUrl')[0]
      cursor = conn.cursor()
      sql = 'INSERT INTO url (host_id, url_url) VALUES (%s,%s) ON DUPLICATE KEY UPDATE host_id = %s'
      if DEBUG : print "Inserting %s : %s" % (host_id, urlandtag)
      cursor.execute(sql, (host_id, urlandtag, host_id))
      conn.commit()
      cursor.close()
