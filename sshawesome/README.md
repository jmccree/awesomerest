## I can't tell you to RTFM if there isn't one.
Here it is. See the before and after. See why I spent 2 hours writing this? :)

###Example server config

    [groupname]
    server="user@server.com;../.ssh/id_rsa;OtherOpt=value&OtherOpt2=value2"
    server2="user@server2.com:2222;server2.pem"

###Example of what's added to ssh config

    #sshawesome#
    Host groupname_server
    User user
    Hostname server.com
    IdentityFile /home/user/keys/../.ssh/id_rsa
    OtherOpt value
    OtherOpt2 value2
    Host groupname_server2
    User user
    Hostname server2.com
    Port 2222
    IdentityFile /home/user/keys/server2.pem
#sshawesome#
