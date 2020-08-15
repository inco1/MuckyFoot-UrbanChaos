cmake_minimum_required(VERSION 3.8)
project(urbanChaos)

file(GLOB MFStd_SOURCES MFStdLib/Source/StdLib/*.cpp)
add_library(mfstd ${MFStd_SOURCES})
target_include_directories(mfstd PUBLIC MFStdLib/Headers)

file(GLOB FALLEN_SRC fallen/Source/*.cpp)
add_executable(fallen ${FALLEN_SRC})
target_include_directories(fallen PRIVATE fallen/ledit/headers fallen/sedit/headers fallen/DDLibrary/Headers fallen/gedit/headers fallen/ddengine/headers fallen/Headers fallen/DDLibrary stubs/Headers)

target_link_libraries(fallen mfstd)