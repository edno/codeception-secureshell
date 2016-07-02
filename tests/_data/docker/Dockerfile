# sshd
#
# VERSION               0.0.2

FROM ubuntu:14.04
MAINTAINER Greg Heitz <g.heitz@yahoo.fr>

RUN apt-get update && apt-get install -y openssh-server
RUN mkdir /var/run/sshd
RUN echo 'root:password' | chpasswd

# udpate SSH config
RUN sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
RUN sed -i 's/PermitEmptyPasswords no/PermitEmptyPasswords yes/' /etc/ssh/sshd_config
RUN sed -i 's/#AuthorizedKeysFile/AuthorizedKeysFile/' /etc/ssh/sshd_config

# SSH login fix. Otherwise user is kicked off after login
RUN sed -i 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' /etc/pam.d/sshd

# additional user
RUN useradd user001
RUN mkdir -p /home/user001/.ssh
COPY keys/user001.pub /home/user001/user001.pub
RUN cat /home/user001/user001.pub >> /home/user001/.ssh/authorized_keys
RUN chown -R user001 /home/user001
RUN chmod 0700 /home/user001/.ssh; chmod 0600 /home/user001/.ssh/authorized_keys
RUN exit

ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]
