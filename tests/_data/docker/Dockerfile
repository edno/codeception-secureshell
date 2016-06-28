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

# SSH login fix. Otherwise user is kicked off after login
RUN sed -i 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' /etc/pam.d/sshd

# additional user
RUN useradd user001
RUN su user001
RUN mkdir ~/.ssh; mkdir ~/.ssh/authorized_keys
COPY keys/user001_rsa.pub /home/user001/.ssh/authorized_keys/id_rsa.pub
RUN chmod -R 0700 /home/user001/.ssh
RUN exit

ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]
