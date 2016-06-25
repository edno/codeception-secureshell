# sshd
#
# VERSION               0.0.1

FROM ubuntu:14.04
MAINTAINER Greg Heitz <g.heitz@yahoo.fr>

RUN apt-get update && apt-get install -y openssh-server
RUN mkdir /var/run/sshd
RUN echo 'root:screencast' | chpasswd
RUN useradd user001
RUN sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
RUN sed -i 's/PermitEmptyPasswords no/PermitEmptyPasswords yes/' /etc/ssh/sshd_config

# SSH login fix. Otherwise user is kicked off after login
RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd

RUN su user001 -c 'mkdir /home/user001/.ssh /home/user001/.ssh/authorized_keys'
COPY ../keys/user001_rsa.pub /home/user001/.ssh/authorized_keys/id_rsa.pub
RUN chmod -R 0700 /home/user001/.ssh

ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]
