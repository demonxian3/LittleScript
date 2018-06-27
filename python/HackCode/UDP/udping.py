#coding: utf-8
import sys
from scapy.all import *

# ָ��һ��udp�˿ڣ�����Է��������߲���û�п���udp�˿ڷ��񣬶Է���ظ��˿ڲ��ɴ��icmp����
# ����Է����������ߣ� ���߸պÿ��˶�Ӧ��UDP ������ô�ǲ���ظ��κ���Ϣ��

if len(sys.argv) < 2:
    sys.exit()

ipaddr = sys.argv[1];

pkt = IP(dst=ipaddr) / UDP(dport=33897)

res = sr1(pkt, timeout=1, verbose=0)

if res and int(res[IP].proto) == 1 :
    print ipaddr + " is alive"
else:
    print ipaddr + " is down"
