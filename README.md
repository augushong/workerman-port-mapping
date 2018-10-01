# workerman-port-mapping

#### 项目介绍
使用workerman实现的端口映射程序,支持内网穿透,支持win的多端口映射.

#### 软件架构
基于workerman及其Channel分布式通讯组件建立的端口映射程序,支持内网穿透,支持同时多端口映射.


#### 安装教程

1. 搭建完整的php环境,把php加到环境变量中
2. 1 (使用git安装)安装git,执行
    git clone https://gitee.com/augushong/workerman-port-mapping.git
3. 配置文件(workerman-port-mapping/config/config.json)
4. 服务端启动
    php server.php start -d
5. 客户端启动 
    php client.php start
    (windows下 请启动:php client_for_win.php)

#### 使用说明

##### 配置文件
1. 简单地配置
```
    {
        "server_ip":"47.88.58.78",
        "server_port":7878,
        "local_ip":"127.0.0.1",
        "local_port":80,
        "channel_port":2206,
        "name":"channel.augushong.com",
        "password":"phpnb",
    }
```
|名称|说明|
|--|--|
|server_ip|服务端地址,客户端需要能连接!一般填外网.|
|server_port|服务端监听的地址,连接这个端口的连接会被发送到本地.|
|local_ip|客户端想要连接的地址,客户端需要能连接!本机地址或者局域网地址.|
|local_port|客户端想要连接的端口,比如本地的80端口.|
|channel_port|客户端和服务端建立通道的端口,一般不用管,为了防止端口冲突而配置.|
|name|本映射的名称,暂不支持.|
|password|加密或者建立端口的字符串,暂不支持.|
    必填项:server_ip,server_port,local_ip,local_port
    可选项:channel_port
    暂不支持:name,password
    其他的配置项会忽略.
    上面的配置在启动后,server端会监听7878端口和2206端口,client链接服务端的2206端口,当server收到连接时,会转发到client,client继续转发到本地ip:80端口.
2. 多个端口配置
```
    {
        "server_ip":"47.88.58.78",
        "server_port":7878,
        "local_ip":"127.0.0.1",
        "local_port":80,
        "channel_port":2206,
        "name":"channel.augushong.com",
        "password":"asd",
        "nat_list":[
            {
                "server_port":888,
                "local_port":80,
                "name":"http"
            },
            {
                "server_port":8188,
                "local_port":810,
                "name":"http"
            }
        ]
    }
```
    增加nat_list配置,则会启动nat_list里的配置,不在启动一级配置的内容,比如上面的例子里,server端会监听2206,888,8188,(一级配置中的7878不会被监听);
    nat_list里缺少的配置项会使用以及配置里的内容,比如上面的例子中每个nat_list都会使用一级配置中的server_ip.

##### 运行
1. 服务端

```
    在服务端中执行命令 php server.php start -d
    环境需要的配置可参考workerman: http://doc.workerman.net/install/requirement.html
```

2.客户端
```
    linux 启动 php client.php start -d
    此时会以后台方式启动
    windows下不支持这种方式,需要命令 php client_for_win.php
    windows下不支持后台方式运行
    注意:如果windows下按照linux的方式启动,不会以后台方式启动,也不会启动nat_list的配置,只会启动一级配置项.
    环境要求可参考workerman
```

#### 协议
    令人不膈应的的MIT

#### 文档
    https://gitee.com/augushong/workerman-port-mapping

#### 彩蛋
    windows10下双击client_for_win.bat启动,点击关闭竟然会自动重启,好惊艳啊,哈哈.
    不过不要慌,连续多点几次就关闭了.